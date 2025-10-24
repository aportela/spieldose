<?php

declare(strict_types=1);

namespace Spieldose\Library\Scanner;

class LibraryScanner
{

    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;
    private \Spieldose\Library\Manager $libraryManager;
    private \Spieldose\Library\Scanner\ID3Scanner $id3Scanner;
    private ?string $validCoverFilenamesPattern;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->libraryManager = new \Spieldose\Library\Manager($dbh, $logger);
        $this->id3Scanner = new \Spieldose\Library\Scanner\ID3Scanner($dbh, $logger);
    }

    public function __destruct() {}

    public function setValidCoverFilenamesPattern(string $pattern)
    {
        $this->validCoverFilenamesPattern = $pattern;
    }

    /**
     * scan (fill DIRECTORY && FILE tables) all library paths
     */
    public function scanLibrary(bool $enqueueID3 = true, ?callable $directoryScanCallback = null, ?callable $fileScanCallback = null)
    {
        foreach ($this->libraryManager->getLibraryPaths() as $currentLibraryPath) {
            $this->scanLibraryPath($currentLibraryPath->pathId, $currentLibraryPath->path, $enqueueID3, $directoryScanCallback, $fileScanCallback);
        }
    }

    /**
     * scan (fill DIRECTORY && FILE tables) custom library path
     */
    public function scanLibraryPath(string $pathId, string $path, bool $enqueueID3 = true, ?callable $directoryScanCallback = null, ?callable $fileScanCallback = null)
    {
        $this->logger->notice("Scanning library path", [$pathId, $path]);
        $path = realpath($path);
        $directories = \Spieldose\Library\FileSystem::getRecursiveDirectories($path);
        $totalDirectories = count($directories);
        for ($d = 0; $d < $totalDirectories; $d++) {
            if ($directoryScanCallback != null) {
                call_user_func($directoryScanCallback, $directories, $totalDirectories, $d);
            }
            $currentDirectory = realpath($directories[$d]);
            $currentDirectoryId = $this->libraryManager->getLibraryPathDirectoryId($currentDirectory);
            $this->logger->debug("Propagating library path directory", [$currentDirectoryId, $currentDirectory]);
            $currentDirectoryFiles = \Spieldose\Library\FileSystem::getDirectoryFiles($currentDirectory);
            $totalCurrentDirectoryFiles = count($currentDirectoryFiles);
            // only add directories with supported files
            if ($totalCurrentDirectoryFiles > 0) {
                $this->logger->debug("Found files for directory", [$totalCurrentDirectoryFiles]);
                $coverFilename = \Spieldose\Library\FileSystem::getCoverFilename($currentDirectory, $this->validCoverFilenamesPattern ?? \Spieldose\Library\FileSystem::VALID_COVER_FILENAMES_DEFAULT_PATTERN);
                $stat = stat($currentDirectory);
                if (empty($currentDirectoryId)) {
                    $currentDirectoryId = \Spieldose\Utils::uuidv4();
                }
                $this->dbh->execute(
                    "
                        INSERT INTO DIRECTORY
                            (id, library_path_id, path, ctime, mtime, cover_filename)
                        VALUES
                            (:id, :library_path_id, :path, :current_timestamp, :mtime, :cover_filename)
                        ON CONFLICT (id) DO
                        UPDATE SET
                            library_path_id = :library_path_id,
                            path = :path,
                            mtime = :mtime,
                            cover_filename = :cover_filename
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $currentDirectoryId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":library_path_id", $pathId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":path", realpath($currentDirectory)),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime']),
                        !empty($coverFilename) ? new \aportela\DatabaseWrapper\Param\StringParam(":cover_filename", $coverFilename) : new \aportela\DatabaseWrapper\Param\NullParam(":cover_filename")
                    ]
                );
                for ($f = 0; $f < $totalCurrentDirectoryFiles; $f++) {
                    if ($fileScanCallback != null) {
                        call_user_func($fileScanCallback, $currentDirectoryFiles, $totalCurrentDirectoryFiles, $f);
                    }
                    $file = realpath($currentDirectoryFiles[$f]);
                    $stat = stat($file);
                    $filename = basename($file);
                    $fileId = $this->libraryManager->getLibraryPathDirectoryFileId($currentDirectoryId, $filename);
                    $this->logger->debug("Propagating file", [$fileId, $filename]);
                    if (empty($fileId)) {
                        $fileId = \Spieldose\Utils::uuidv4();
                    }
                    $this->dbh->execute(
                        "
                            INSERT INTO FILE
                                (id, directory_id, name, size, ctime, mtime)
                            VALUES
                                (:id, :directory_id, :name, :size, :current_timestamp, :mtime)
                            ON CONFLICT (id) DO
                            UPDATE SET
                                directory_id = :directory_id,
                                name = :name,
                                size = :size,
                                mtime = :mtime
                        ",
                        [
                            new \aportela\DatabaseWrapper\Param\StringParam(":id", $fileId),
                            new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $currentDirectoryId),
                            new \aportela\DatabaseWrapper\Param\StringParam(":name", $filename),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":size", filesize($file)),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime'])
                        ]
                    );
                    if ($enqueueID3) {
                        $this->id3Scanner->enqueueFile($fileId);
                    }
                }
            } else if (! empty($currentDirectoryId)) {
                $this->logger->debug("No found directory files", [$totalCurrentDirectoryFiles]);
                // existent directory with no files => remove
                $this->dbh->execute(
                    "
                        DELETE FROM DIRECTORY
                        WHERE id = :id
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $currentDirectoryId)
                    ]
                );
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Spieldose\Library\Scanner;

class LibraryScanner
{
    private readonly \Spieldose\Library\Manager $libraryManager;

    private readonly \Spieldose\Library\Scanner\ID3Scanner $id3Scanner;

    private ?string $validCoverFilenamesPattern = null;

    public function __construct(private readonly \aportela\DatabaseWrapper\DB $db, private readonly \Psr\Log\LoggerInterface $logger)
    {
        $this->libraryManager = new \Spieldose\Library\Manager($this->db, $this->logger);
        $this->id3Scanner = new \Spieldose\Library\Scanner\ID3Scanner($this->db, $this->logger);
    }

    public function setValidCoverFilenamesPattern(string $pattern): void
    {
        $this->validCoverFilenamesPattern = $pattern;
    }

    /**
     * scan (fill DIRECTORY && FILE tables) all library paths
     */
    public function scanLibrary(bool $enqueueID3 = true, bool $force = false, ?callable $directoryScanCallback = null, ?callable $fileScanCallback = null): float
    {
        $this->logger->info("LibraryScanner::scanLibraryPath");
        $totalScanTime = 0;
        foreach ($this->libraryManager->getLibraryPaths() as $currentLibraryPath) {
            $this->logger->debug("LibraryScanner::scanLibraryPath - Current path", [$currentLibraryPath->pathId, $currentLibraryPath->path]);
            $totalScanTime += $this->scanLibraryPath($currentLibraryPath->pathId, $currentLibraryPath->path, $enqueueID3, $force, $directoryScanCallback, $fileScanCallback);
        }

        return ($totalScanTime);
    }

    /**
     * scan (fill DIRECTORY && FILE tables) custom library path
     */
    public function scanLibraryPath(string $pathId, string $path, bool $enqueueID3 = true, bool $force = false, ?callable $directoryScanCallback = null, ?callable $fileScanCallback = null): float
    {
        $this->logger->info("LibraryScanner::scanLibraryPath");
        $scanStartTime = microtime(true);
        $path = realpath($path);
        $directories = \Spieldose\Library\FileSystem::getRecursiveDirectories($path);
        $totalDirectories = count($directories);
        $this->logger->debug("ID3Scanner::processPendingQueue - Total directories: ", [$totalDirectories]);
        for ($d = 0; $d < $totalDirectories; ++$d) {
            if ($directoryScanCallback != null) {
                call_user_func($directoryScanCallback, $directories, $totalDirectories, $d);
            }

            $currentDirectory = realpath($directories[$d]);
            $currentDirectoryId = $this->libraryManager->getLibraryPathDirectoryId($currentDirectory);
            $this->logger->debug("ID3Scanner::processPendingQueue - Current directory: ", [$currentDirectoryId, $currentDirectory]);
            $currentDirectoryFiles = \Spieldose\Library\FileSystem::getDirectoryFiles($currentDirectory);
            $totalCurrentDirectoryFiles = count($currentDirectoryFiles);
            $this->logger->debug("ID3Scanner::processPendingQueue - Total directory files: ", [$totalCurrentDirectoryFiles]);
            // only add directories with supported files
            $coverFilename = \Spieldose\Library\FileSystem::getCoverFilename($currentDirectory, $this->validCoverFilenamesPattern ?? \Spieldose\Library\FileSystem::VALID_COVER_FILENAMES_DEFAULT_PATTERN);
            $stat = stat($currentDirectory);
            if (in_array($currentDirectoryId, [null, '', '0'], true)) {
                $currentDirectoryId = \Spieldose\Utils::uuidv4();
            }

            $this->logger->debug("ID3Scanner::processPendingQueue - Saving current directory: ", [$currentDirectoryId, $currentDirectory]);
            $this->db->execute(
                "
                        INSERT INTO DIRECTORY
                            (id, library_path_id, path, cover_filename, ctime, mtime)
                        VALUES
                            (:id, :library_path_id, :path, :cover_filename, :current_timestamp, :mtime)
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
                    in_array($coverFilename, [null, '', '0'], true) ? new \aportela\DatabaseWrapper\Param\NullParam(":cover_filename") : new \aportela\DatabaseWrapper\Param\StringParam(":cover_filename", $coverFilename),
                ]
            );
            for ($f = 0; $f < $totalCurrentDirectoryFiles; ++$f) {
                if ($fileScanCallback != null) {
                    call_user_func($fileScanCallback, $currentDirectoryFiles, $totalCurrentDirectoryFiles, $f);
                }

                $file = realpath($currentDirectoryFiles[$f]);
                $stat = stat($file);
                $filename = basename($file);
                $fileId = $this->libraryManager->getLibraryPathDirectoryFileId($currentDirectoryId, $filename);
                $this->logger->debug("Propagating file", [$fileId, $filename]);
                if (in_array($fileId, [null, '', '0'], true)) {
                    $fileId = \Spieldose\Utils::uuidv4();
                }

                $this->logger->debug("ID3Scanner::processPendingQueue - Saving current file: ", [$fileId, $filename]);
                $this->db->execute(
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
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime']),
                    ]
                );
                if ($enqueueID3) {
                    $this->id3Scanner->enqueueFile($fileId, $force);
                }
            }
        }

        return (microtime(true) - $scanStartTime);
    }
}

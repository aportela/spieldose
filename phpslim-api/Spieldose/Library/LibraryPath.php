<?php

declare(strict_types=1);

namespace Spieldose\Library;

class LibraryPath
{
    private \aportela\DatabaseWrapper\DB $dbh;
    /**
     * @var array<string, string>
     */
    public array $items = [];

    public function __construct(\aportela\DatabaseWrapper\DB $dbh)
    {
        $this->dbh = $dbh;
    }

    public function __destruct() {}

    /**
     * checks for path existence (returns path id || null)
     */
    public function getPathId(string $path): ?string
    {
        $results = $this->dbh->query(
            " SELECT id FROM LIBRARY_PATH WHERE path = :path ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":path", realpath($path)),
            ]
        );
        if (count($results) == 1) {
            return ($results[0]->id);
        } else {
            return (null);
        }
    }

    /**
     * this is used for preventing duplicated (children) items (ex: adding paths: "c:\music\" && "c:\music\jazz")
     */
    public function isPathContainedOnCurrentPaths(string $path): bool
    {
        $results = $this->dbh->query(" SELECT path FROM LIBRARY_PATH ORDER BY path ");
        foreach ($results as $result) {
            if (str_starts_with(realpath($path), $result->path)) {
                return (true);
            }
        }
        return (false);
    }

    public function addPath(string $path): string
    {
        $this->dbh->execute(
            " INSERT INTO LIBRARY_PATH (id, path, ctime, mtime) VALUES (:id, :path, :current_timestamp, :current_timestamp) ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", \Spieldose\Utils::uuidv4()),
                new \aportela\DatabaseWrapper\Param\StringParam(":path", realpath($path)),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000))
            ]
        );
        $pathId = $this->dbh->query(
            " SELECT id FROM LIBRARY_PATH WHERE path = :path ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":path", realpath($path))
            ]
        )[0]->id;
        return ($pathId);
    }

    public function removePath(string $path): bool
    {
        $pathId = $this->getPathId($path);
        if (! empty($pathId)) {
            // DIRECTORY && FILE related rows are deleted on cascade
            $this->dbh->execute(
                " DELETE FROM LIBRARY_PATH WHERE id = :id ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $pathId)
                ]
            );
            return (true);
        } else {
            return (false);
        }
    }

    private function getDirectoryId(string $path): ?string
    {
        $results = $this->dbh->query(
            " SELECT id FROM DIRECTORY WHERE path = :path ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":path", realpath($path)),
            ]
        );
        if (count($results) == 1) {
            return ($results[0]->id);
        } else {
            return (null);
        }
    }

    private function getFileId(string $directoryId, string $name): ?string
    {
        $results = $this->dbh->query(
            " SELECT id FROM FILE WHERE directory_id = :directory_id AND name = :name ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $directoryId),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $name),
            ]
        );
        if (count($results) == 1) {
            return ($results[0]->id);
        } else {
            return (null);
        }
    }
    public function scanPath(string $pathId, string $path)
    {
        $path = realpath($path);
        $directories = \Spieldose\Library\FileSystem::getRecursiveDirectories($path);
        foreach ($directories as $directory) {
            $directory = realpath($directory);
            $coverFilename = \Spieldose\Library\FileSystem::getCoverFilename($directory);
            $stat = stat($directory);
            $directoryId = $this->getDirectoryId($directory);
            if (empty($directoryId)) {
                $directoryId = \Spieldose\Utils::uuidv4();
                $this->dbh->execute(
                    " INSERT INTO DIRECTORY (id, library_path_id, path, mtime, cover_filename) VALUES (:id, :library_path_id, :path, :mtime, :cover_filename) ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $directoryId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":library_path_id", $pathId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":path", realpath($directory)),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime']),
                        !empty($coverFilename) ? new \aportela\DatabaseWrapper\Param\StringParam(":cover_filename", $coverFilename) : new \aportela\DatabaseWrapper\Param\NullParam(":cover_filename")
                    ]
                );
            } else {
                $this->dbh->execute(
                    " UPDATE DIRECTORY SET mtime = :mtime, cover_filename = :cover_filename WHERE id = :id",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $directoryId),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime']),
                        !empty($coverFilename) ? new \aportela\DatabaseWrapper\Param\StringParam(":cover_filename", $coverFilename) : new \aportela\DatabaseWrapper\Param\NullParam(":cover_filename")
                    ]
                );
            }
            $files = \Spieldose\Library\FileSystem::getDirectoryFiles($directory);
            foreach ($files as $file) {
                $file = realpath($file);
                $stat = stat($file);
                $filename = basename($file);
                $fileId = $this->getFileId($directoryId, $filename);
                echo $fileId;
                if (empty($fileId)) {
                    $fileId = \Spieldose\Utils::uuidv4();
                    $this->dbh->execute(
                        " INSERT INTO FILE (id, directory_id, name, size, mtime) VALUES (:id, :directory_id, :name, :size, :mtime)  ",
                        array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":id", $fileId),
                            new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $directoryId),
                            new \aportela\DatabaseWrapper\Param\StringParam(":name", $filename),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":size", filesize($file)),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime'])
                        )
                    );
                } else {
                    $this->dbh->execute(
                        " UPDATE FILE SET size= :size, mtime = :mtime WHERE id = :id ",
                        array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":id", $fileId),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":size", filesize($file)),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime'])
                        )
                    );
                }
            }
        }
    }
}

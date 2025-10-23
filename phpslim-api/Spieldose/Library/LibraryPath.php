<?php

declare(strict_types=1);

namespace Spieldose\Library;

class LibraryPath
{
    private \aportela\DatabaseWrapper\DB $dbh;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh)
    {
        $this->dbh = $dbh;
    }

    public function __destruct() {}

    /**
     * checks for library path existence (returns path id || null)
     */
    private function getPathId(string $path): ?string
    {
        $results = $this->dbh->query(
            "
                SELECT
                    id
                FROM LIBRARY_PATH
                WHERE path = :path
            ",
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
     * this is used for preventing duplicated (children) library paths (ex: adding paths: "c:\music\" && "c:\music\jazz")
     */
    public function isPathContainedOnCurrentPaths(string $path): bool
    {
        $path = realpath($path);
        $results = $this->dbh->query(
            "
                SELECT
                    path
                FROM LIBRARY_PATH
                WHERE path <> :path
                ORDER BY path
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":path", $path),
            ]
        );
        foreach ($results as $result) {
            // TODO: str_starts_with works with unicode ?
            if (str_starts_with($path, $result->path)) {
                return (true);
            }
        }
        return (false);
    }

    /**
     * add / update library path
     */
    public function addPath(string $path): string
    {
        $path = realpath($path);
        $pathId = $this->getPathId($path);
        if (empty($pathId)) {
            $pathId = \Spieldose\Utils::uuidv4();
        }
        $stat = stat($path);
        $this->dbh->execute(
            "
                    INSERT INTO LIBRARY_PATH
                        (id, path, ctime, mtime)
                    VALUES
                        (:id, :path, :current_timestamp, :mtime)
                    ON CONFLICT (id) DO
                    UPDATE SET
                        mtime = :mtime;
                ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $pathId),
                new \aportela\DatabaseWrapper\Param\StringParam(":path", $path),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime'])
            ]
        );
        return ($pathId);
    }

    /**
     * remove library path
     */
    public function removePath(string $path): bool
    {
        $pathId = $this->getPathId($path);
        if (! empty($pathId)) {
            // DIRECTORY && FILE related rows are deleted on cascade
            $this->dbh->execute(
                "
                    DELETE FROM LIBRARY_PATH
                    WHERE id = :id
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $pathId)
                ]
            );
            return (true);
        } else {
            return (false);
        }
    }

    /**
     * return all library paths
     * @return array<mixed>
     */
    public function getPaths(): array
    {
        return (
            $this->dbh->query(
                "
                SELECT
                    id, path
                FROM LIBRARY_PATH
                ORDER BY path
            "
            )
        );
    }

    /**
     * @return array<mixed>
     */
    public function getLibraryPathDirectories(string $libraryPathId): array
    {
        return (
            $this->dbh->query(
                "
                    SELECT
                        id, path
                    FROM DIRECTORY
                    WHERE library_path_id = :library_path_id
                    ORDER BY path
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":library_path_id", $libraryPathId)
                ]
            )
        );
    }

    private function getDirectoryId(string $path): ?string
    {
        $results = $this->dbh->query(
            "
                SELECT
                    id
                FROM DIRECTORY
                WHERE path = :path
            ",
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
     * @return array<mixed>
     */
    public function getLibraryPathDirectoryFiles(string $libraryPathDirectoryId): array
    {
        return (
            $this->dbh->query(
                "
                    SELECT
                        FILE.id, DIRECTORY.path, FILE.name
                    FROM DIRECTORY
                    INNER JOIN FILE ON FILE.directory_id = DIRECTORY.id
                    WHERE DIRECTORY.id = :directory_id
                    ORDER BY path
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $libraryPathDirectoryId)
                ],
                function ($rows) {
                    array_map(
                        function ($item) {
                            $item->fullPath = $item->path . DIRECTORY_SEPARATOR . $item->name;
                            unset($item->name);
                            unset($item->path);
                            return $item;
                        },
                        $rows
                    );
                }
            )
        );
    }

    private function getFileId(string $directoryId, string $name): ?string
    {
        $results = $this->dbh->query(
            "
                SELECT
                    id
                FROM FILE
                WHERE
                    directory_id = :directory_id
                AND
                    name = :name
            ",
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

    /**
     * scan custom library path
     */
    public function scanPath(string $pathId, string $path)
    {
        $path = realpath($path);
        $directories = \Spieldose\Library\FileSystem::getRecursiveDirectories($path);
        foreach ($directories as $directory) {
            $directory = realpath($directory);
            $directoryId = $this->getDirectoryId($directory);
            $files = \Spieldose\Library\FileSystem::getDirectoryFiles($directory);
            $totalFiles = count($files);
            // only add directories with supported files
            if ($totalFiles > 0) {
                $coverFilename = \Spieldose\Library\FileSystem::getCoverFilename($directory);
                $stat = stat($directory);
                if (empty($directoryId)) {
                    $directoryId = \Spieldose\Utils::uuidv4();
                }
                $this->dbh->execute(
                    "
                        INSERT INTO DIRECTORY
                            (id, library_path_id, path, mtime, cover_filename)
                        VALUES
                            (:id, :library_path_id, :path, :mtime, :cover_filename)
                        ON CONFLICT (id) DO
                        UPDATE SET
                            library_path_id = :library_path_id,
                            path = :path,
                            mtime = :mtime,
                            cover_filename = :cover_filename
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $directoryId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":library_path_id", $pathId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":path", realpath($directory)),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime']),
                        !empty($coverFilename) ? new \aportela\DatabaseWrapper\Param\StringParam(":cover_filename", $coverFilename) : new \aportela\DatabaseWrapper\Param\NullParam(":cover_filename")
                    ]
                );
                foreach ($files as $file) {
                    $file = realpath($file);
                    $stat = stat($file);
                    $filename = basename($file);
                    $fileId = $this->getFileId($directoryId, $filename);
                    if (empty($fileId)) {
                        $fileId = \Spieldose\Utils::uuidv4();
                    }
                    $this->dbh->execute(
                        "
                                INSERT INTO FILE
                                    (id, directory_id, name, size, mtime)
                                VALUES
                                    (:id, :directory_id, :name, :size, :mtime)
                                ON CONFLICT (id) DO
                                UPDATE SET
                                    directory_id = :directory_id,
                                    name = :name,
                                    size = :size,
                                    mtime = :mtime
                            ",
                        [
                            new \aportela\DatabaseWrapper\Param\StringParam(":id", $fileId),
                            new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $directoryId),
                            new \aportela\DatabaseWrapper\Param\StringParam(":name", $filename),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":size", filesize($file)),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime'])
                        ]
                    );
                }
            } else if (! empty($directoryId)) {
                // existent directory with no files => remove
                $this->dbh->execute(
                    "
                        DELETE FROM DIRECTORY
                        WHERE id = :id
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $directoryId)
                    ]
                );
            }
        }
    }

    /**
     * scan all library paths
     */
    public function scanLibrary()
    {
        $paths = $this->getPaths();
        foreach ($paths as $path) {
            $this->scanPath($path->id, $path->path);
        }
    }

    /**
     * full list (id/path) of library files (used for clean orphaned data)
     * return array<mixed>
     */
    public function getAllLibraryDirectoryFiles(): array
    {
        return (
            $this->dbh->query(
                "
                    SELECT
                        FILE.id, DIRECTORY.path, FILE.name
                    FROM FILE
                    INNER JOIN DIRECTORY ON DIRECTORY.ID = FILE.directory_id
                    ORDER BY DIRECTORY.path, FILE.name
                ",
                [],
                function ($rows) {
                    array_map(
                        function ($item) {
                            $item->fullPath = $item->path . DIRECTORY_SEPARATOR . $item->name;
                            unset($item->name);
                            unset($item->path);
                            return $item;
                        },
                        $rows
                    );
                }
            )
        );
    }
}

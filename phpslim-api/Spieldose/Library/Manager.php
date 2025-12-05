<?php

declare(strict_types=1);

namespace Spieldose\Library;

class Manager
{
    public function __construct(private readonly \aportela\DatabaseWrapper\DB $db, private readonly \Psr\Log\LoggerInterface $logger) {}

    /**
     * checks for library path existence (returns path id || null)
     */
    private function getLibraryPathId(string $path): ?string
    {
        $results = $this->db->query(
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
        if (count($results) === 1) {
            return ($results[0]->id);
        } else {
            return (null);
        }
    }

    /**
     * this is used for preventing duplicated (children) library paths (ex: adding paths: "c:\music\" && "c:\music\jazz")
     */
    public function isPathContainedOnCurrentLibraryPaths(string $path): bool
    {
        $path = realpath($path);
        $results = $this->db->query(
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
        return array_any($results, fn($result): bool => str_starts_with($path, (string) $result->path));
    }

    /**
     * add / update library path
     */
    public function addLibraryPath(string $path, string $name): string
    {
        $path = realpath($path);
        $pathId = $this->getLibraryPathId($path);
        if (in_array($pathId, [null, '', '0'], true)) {
            $pathId = \Spieldose\Utils::uuidv4();
        }

        if (mb_strlen($name) > 128) {
            throw new \InvalidArgumentException("max name length (128) exceed");
        }

        $stat = stat($path);
        $this->logger->info("Setting library path", [$pathId, $path]);
        $this->db->execute(
            "
                    INSERT INTO LIBRARY_PATH
                        (id, path, name, ctime, mtime)
                    VALUES
                        (:id, :path, :name, :current_timestamp, :mtime)
                    ON CONFLICT (id) DO
                    UPDATE SET
                        name = :name,
                        mtime = :mtime;
                ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $pathId),
                new \aportela\DatabaseWrapper\Param\StringParam(":path", $path),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $name),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime']),
            ]
        );
        return ($pathId);
    }

    /**
     * remove library path
     */
    public function removeLibraryPath(string $path): bool
    {
        $pathId = $this->getLibraryPathId($path);
        if (!in_array($pathId, [null, '', '0'], true)) {
            $this->logger->info("Removing library path", [$pathId, $path]);
            // DIRECTORY && FILE related rows are deleted on cascade
            $this->db->execute(
                "
                    DELETE FROM LIBRARY_PATH
                    WHERE id = :id
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $pathId),
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
    public function getLibraryPaths(): array
    {
        return (
            $this->db->query(
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
            $this->db->query(
                "
                    SELECT
                        id, path
                    FROM DIRECTORY
                    WHERE library_path_id = :library_path_id
                    ORDER BY path
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":library_path_id", $libraryPathId),
                ]
            )
        );
    }

    public function getLibraryPathDirectoryId(string $path): ?string
    {
        $results = $this->db->query(
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
        if (count($results) === 1) {
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
            $this->db->query(
                "
                    SELECT
                        FILE.id, DIRECTORY.path, FILE.name
                    FROM DIRECTORY
                    INNER JOIN FILE ON FILE.directory_id = DIRECTORY.id
                    WHERE DIRECTORY.id = :directory_id
                    ORDER BY path
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $libraryPathDirectoryId),
                ],
                function ($rows): void {
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

    public function getLibraryPathDirectoryFileId(string $directoryId, string $name): ?string
    {
        $results = $this->db->query(
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
        if (count($results) === 1) {
            return ($results[0]->id);
        } else {
            return (null);
        }
    }

    public function removeLibraryPathDirectoryFile(string $id): void
    {
        $this->logger->notice("Removing library path directory file", [$id]);
        $this->db->execute(
            "
                DELETE FROM FILE
                WHERE id = :id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $id),
            ]
        );
    }


    /**
     * scan all library paths
     */
    public function scanLibrary(): void
    {
        $this->logger->notice("Scanning full library");
        $paths = $this->getLibraryPaths();
        foreach ($paths as $path) {
            $this->scanLibraryPath($path->id, $path->path);
        }
    }

    /**
     * full list (id/path) of library files (used for clean orphaned data)
     * return array<mixed>
     */
    public function getAllLibraryPathDirectoryFiles(): array
    {
        return (
            $this->db->query(
                "
                    SELECT
                        FILE.id, DIRECTORY.path, FILE.name
                    FROM FILE
                    INNER JOIN DIRECTORY ON DIRECTORY.ID = FILE.directory_id
                    ORDER BY DIRECTORY.path, FILE.name
                ",
                [],
                function ($rows): void {
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

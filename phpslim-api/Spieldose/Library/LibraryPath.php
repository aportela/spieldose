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
}

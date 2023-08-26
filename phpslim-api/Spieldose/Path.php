<?php

declare(strict_types=1);

namespace Spieldose;

class Path
{
    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    public static function getTree(\aportela\DatabaseWrapper\DB $dbh): array
    {
        $nodes = [];
        $items = $dbh->query("
            SELECT D.id AS id, D.path AS fullPath, COALESCE(TMP_COUNT.total_files, 0) AS totalFiles
            FROM DIRECTORY D
            LEFT JOIN (
                SELECT COUNT(FILE.id) AS total_files, FILE.directory_id FROM FILE GROUP BY FILE.directory_id
            ) TMP_COUNT ON TMP_COUNT.directory_id = D.id
        ");
        $fullPaths = [];
        foreach ($items as $item) {
            $fullPaths[$item->id] = $item->fullPath;
        }
        // https://stackoverflow.com/a/61508907


        return ($nodes);
    }
}

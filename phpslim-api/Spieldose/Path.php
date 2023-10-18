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
        $items = $dbh->query("
            SELECT D.id AS id, D.path AS fullPath, COALESCE(TMP_COUNT.total_files, 0) AS totalFiles
            FROM DIRECTORY D
            LEFT JOIN (
                SELECT COUNT(FILE.id) AS total_files, FILE.directory_id FROM FILE GROUP BY FILE.directory_id
            ) TMP_COUNT ON TMP_COUNT.directory_id = D.id
            ORDER BY D.path COLLATE NOCASE ASC
        ");
        $fullPaths = [];
        foreach ($items as $item) {
            $fullPaths[$item->fullPath] = $item;
        }


        $tree = [];
        foreach (array_keys($fullPaths) as $path) {
            // https://stackoverflow.com/a/53322666
            $node = &$tree;
            $parts = explode(DIRECTORY_SEPARATOR, $path);
            foreach ($parts as $index => $level) {
                $newNode = array_search($level, array_column($node, "name") ?? []);

                if ($newNode === false) {
                    $id = null;
                    $totalFiles = 0;
                    $completePath = implode(DIRECTORY_SEPARATOR, array_slice($parts, 0, $index + 1));
                    if (array_key_exists($completePath, $fullPaths)) {
                        $totalFiles = $fullPaths[$completePath]->totalFiles;
                        $id = $fullPaths[$completePath]->id;
                    } else {
                        //$id = \Spieldose\Utils::uuidv4();
                    }
                    /*
                    if ($i) {
                        //$i->totalFiles;
                        //$totalFiles = $fullPaths[$completePath];
                    }
                    */
                    $newNode = array_push($node, ["hash" => sha1($completePath), "id" => $id, "name" => $level, "completePath" => $completePath, "children" => [], "totalFiles" => $totalFiles]) - 1;
                }
                $node = &$node[$newNode]["children"];
            }
        }
        return ($tree);
    }

    public static function getTrackIds(\aportela\DatabaseWrapper\DB $dbh, string $id): array
    {
        $query = "";
        $params = [];
        $query = "
                SELECT F.id
                FROM FILE F
                WHERE F.directory_id = :path_id
                ORDER BY F.name
            ";
        $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":path_id", $id);
        $data = $dbh->query($query, $params);
        $ids = [];
        foreach ($data as $item) {
            $ids[] = $item->id;
        }
        return ($ids);
    }
}

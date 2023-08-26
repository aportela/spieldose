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

    public static function search(\aportela\DatabaseWrapper\DB $dbh, array $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $params = array();
        $filterConditions = array();
        if (isset($filter["path"]) && !empty($filter["path"])) {
            $filterConditions[] = " D.path LIKE :path";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":path", "%" . $filter["path"] . "%");
        }
        $fieldDefinitions = [
            "id" => "D.id",
            "path" => "D.path",
            "totalFiles" => "COALESCE(TMP_COUNT.total_files, 0)"
        ];

        $fieldCountDefinition = [
            "totalResults" => " COUNT(D.id)"
        ];

        $afterBrowseFunction = function ($data) {
            $tree = [];
            $data->items = array_map(
                function ($result) {
                    $result->children = [];
                    return ($result);
                },
                $data->items
            );
            foreach ($data->items as $item) {
                $tree[] = $item;
            }
            $data->items = $tree;
        };

        $filter = new \aportela\DatabaseBrowserWrapper\Filter();

        $browser = new \aportela\DatabaseBrowserWrapper\Browser($dbh, $fieldDefinitions, $fieldCountDefinition, $pager, $sort, $filter, $afterBrowseFunction);
        foreach ($params as $param) {
            $browser->addDBQueryParam($param);
        }
        $query = sprintf(
            "
                SELECT
                    %s
                FROM DIRECTORY D
                LEFT JOIN (
                    SELECT COUNT(FILE.id) AS total_files, FILE.directory_id FROM FILE GROUP BY FILE.directory_id
                ) TMP_COUNT ON TMP_COUNT.directory_id = D.id
                %s
                %s
                %s
            ",
            $browser->getQueryFields(),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
            $browser->getQuerySort(),
            $pager->getQueryLimit()
        );
        $queryCount = sprintf(
            "
                SELECT
                    %s
                FROM DIRECTORY D
                %s
            ",
            $browser->getQueryCountFields(),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null
        );
        $data = $browser->launch($query, $queryCount);
        return ($data);
    }
}

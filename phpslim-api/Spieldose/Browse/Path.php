<?php

declare(strict_types=1);

namespace Spieldose\Browse;

class Path extends \Spieldose\Browse\Base
{
    public function browse(\aportela\DatabaseBrowserWrapper\Pager $pager, \aportela\DatabaseBrowserWrapper\Filter $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, bool $skipCount = false): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $this->pager = $pager;
        $this->filter = $filter;
        $this->sort = $sort;
        $this->fieldDefinitions = [
            "id" => "DIRECTORY.id",
            "name" => "DIRECTORY.path",
            "totalFiles" => "COUNT(FILE.id)"
        ];
        $this->fieldCountDefinition = [];
        $afterBrowse = function (\aportela\DatabaseBrowserWrapper\BrowserResults $data) {
            array_map(
                function (object $item): object {
                    if (property_exists($item, "totalTracks") && is_numeric($item->totalTracks)) {
                        $item->totalTracks = intval($item->totalTracks);
                    }
                    return ($item);
                },
                $data->items
            );
        };
        $browser = new \aportela\DatabaseBrowserWrapper\Browser(
            $this->dbh,
            $this->fieldDefinitions,
            $this->fieldCountDefinition,
            $this->pager,
            $this->sort,
            $this->filter,
            $afterBrowse
        );
        $queryConditions = [];
        $params = [];
        if ($filter->hasParam("name") && is_string($filter->getParamValue("name"))) {
            $queryConditions[] = sprintf(" DIRECTORY.path LIKE %s ", ":name");
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":name",  "%" . $filter->getParamValue("name") . "%");
        }
        $whereCondition = $queryConditions !== [] ? " WHERE " .  implode(" AND ", $queryConditions) : "";
        $browser->addDBQueryParams($params);
        $query = $browser->buildQuery(
            sprintf(
                "
                    SELECT %%s
                    FROM DIRECTORY
                    LEFT JOIN FILE ON FILE.directory_id = DIRECTORY.id
                    GROUP BY DIRECTORY.id, DIRECTORY.path
                    %s
                    %%s
                    %%s
                ",
                $whereCondition
            )
        );
        return ($browser->launch($query, "", true));
    }

    public function getLibraries(): array
    {
        return ($this->dbh->query(
            "
                SELECT
                    LIBRARY_PATH.id, LIBRARY_PATH.name
                FROM LIBRARY_PATH
                ORDER BY LIBRARY_PATH.name
            "
        ));
    }

    public function getTree(string $libraryId)
    {
        $afterQueryFunction = function ($rows): void {
            array_map(
                function ($item) {
                    if (property_exists($item, "totalFiles")) {
                        $item->totalFiles = intval($item->totalFiles);
                    }
                    return ($item);
                },
                $rows
            );
        };
        $results = $this->dbh->query(
            "
                    SELECT
                        :directory_separator || REPLACE(DIRECTORY.path, LIBRARY_PATH.path, LIBRARY_PATH.name || :directory_separator) AS label, DIRECTORY.id AS id, COUNT(FILE.id) AS totalFiles
                    FROM DIRECTORY
                    LEFT JOIN LIBRARY_PATH ON LIBRARY_PATH.id = DIRECTORY.library_path_id
                    LEFT JOIN FILE ON FILE.directory_id = DIRECTORY.id
                    WHERE LIBRARY_PATH.id = :id
                    GROUP BY 1
                    ORDER BY DIRECTORY.path
                ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $libraryId),
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_separator", DIRECTORY_SEPARATOR)
            ],
            $afterQueryFunction
        );
        $tree = [];
        foreach ($results as $item) {
            $subDirectories = array_filter(explode(DIRECTORY_SEPARATOR, $item->label));
            $currentTreeNode = &$tree;
            foreach ($subDirectories as $index => $subDirectory) {
                $foundNode = null;
                foreach ($currentTreeNode as $node) {
                    if ($node->label === $subDirectory) {
                        $foundNode = $node;
                        break;
                    }
                }
                if (!$foundNode) {
                    $foundNode = (object)[
                        'label' => $subDirectory,
                        'id' => $item->id,
                        'totalFiles' => $item->totalFiles,
                        'children' => []
                    ];
                    $currentTreeNode[] = $foundNode;
                }
                $currentTreeNode = &$foundNode->children;
            }
        }
        return ($tree);
    }

    public function getPathCoverLocalPath(string $pathId): ?string
    {
        $results = $this->dbh->query(
            "
                    SELECT
                        DIRECTORY.path, DIRECTORY.cover_filename
                    FROM DIRECTORY
                    WHERE
                        DIRECTORY.id = :id
                ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $pathId)
            ]
        );
        if (count($results) == 1 && ! empty($results[0]->cover_filename)) {
            return ($results[0]->path . DIRECTORY_SEPARATOR . $results[0]->cover_filename);
        } else {
            return (null);
        }
    }
}

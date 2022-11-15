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

    /**
     * search paths
     *
     * @param \Spieldose\Database\DB $dbh database handler
     * @param int $page return results from this page
     * @param int $resultsPage number of results / page
     * @param array $filter the condition filter
     * @param string $order results order
     */
    public static function search(\aportela\DatabaseWrapper\DB $dbh, \Spieldose\Helper\Pager $pager, \Spieldose\Helper\Sort $sort, array $filter = array())
    {
        $data = new \Spieldose\Helper\BrowserResults($pager, $sort, []);
        $queryParams = [];
        if (isset($filter["q"]) && !empty($filter["q"])) {
            $queryParams[] = new \aportela\DatabaseWrapper\Param\StringParam(":query", "%" . $filter["q"] . "%");
        }
        $data->items = $dbh->query(
            sprintf(
                "
                    SELECT
                        DIRECTORIES.ID AS id,
                        DIRECTORIES.PATH as path,
                        COUNT(FILES.ID) AS totalFiles
                    FROM DIRECTORIES
                    INNER JOIN FILES ON FILES.DIRECTORY_ID = DIRECTORIES.ID
                    %s
                    GROUP BY DIRECTORIES.ID
                    ORDER BY path %s
                    %s
                ",
                count($queryParams) == 1 ? " WHERE DIRECTORIES.PATH LIKE :query " : null,
                $sort->order == \Spieldose\Helper\Sort::ASCENDING_ORDER ? \Spieldose\Helper\Sort::ASCENDING_ORDER : \Spieldose\Helper\Sort::DESCENDING_ORDER,
                $pager->resultsPage > 0 ? sprintf(" LIMIT %d, %d", $pager->getSQLQueryLimitFrom(), $pager->resultsPage) : null
            ),
            $queryParams
        );
        $data->pager->setTotalResults(count($data->items));
        if ($data->pager->currentPage > 1 || $data->pager->totalResults >= $pager->resultsPage) {
            $tmpCountResults = $dbh->query(
                sprintf(
                    "
                        SELECT COUNT(DIRECTORIES.ID) AS TOTAL
                        FROM DIRECTORIES
                        %s
                    ",
                    count($queryParams) == 1 ? " WHERE DIRECTORIES.PATH LIKE :query " : null,
                ),
                $queryParams
            );
            if (count($tmpCountResults) == 1) {
                $data->pager->setTotalResults($tmpCountResults[0]->TOTAL);
            }
        }
        return ($data);
    }
}

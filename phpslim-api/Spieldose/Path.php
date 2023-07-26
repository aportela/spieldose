<?php
    declare(strict_types=1);

    namespace Spieldose;

    class Path {
	    public function __construct () { }

        public function __destruct() { }

        /**
         * search paths
         *
         * @param \Spieldose\Database\DB $dbh database handler
         * @param int $page return results from this page
         * @param int $resultsPage number of results / page
         * @param array $filter the condition filter
         * @param string $order results order
         */
        public static function search(\Spieldose\Database\DB $dbh, int $page = 1, int $resultsPage = 16, array $filter = array(), string $order = "") {
            $whereCondition = "";
            $params = array();
            if (isset($filter)) {
                $conditions = array();
                if (isset($filter["name"]) && ! empty($filter["name"])) {
                    $params[] = (new \Spieldose\Database\DBParam())->str(":name", $filter["name"]);
                    $conditions[] = ' F.base_path LIKE :name ';
                }
                if (isset($filter["partialName"]) && ! empty($filter["partialName"])) {
                    $params[] = (new \Spieldose\Database\DBParam())->str(":partialName", "%" . $filter["partialName"] . "%");
                    $conditions[] = ' F.base_path LIKE :partialName ';
                }
                $whereCondition = count($conditions) > 0 ? " WHERE " .  implode(" AND ", $conditions) : "";
            }
            $queryCount = '
                SELECT
                    COUNT(DISTINCT F.base_path) AS total
                FROM FILE F
                ' . $whereCondition . '
            ';
            $result = $dbh->query($queryCount, $params);
            $data = new \stdClass();
            $data->actualPage = $page;
            $data->resultsPage = $resultsPage;
            $data->totalResults = $result[0]->total;
            $data->totalPages = ceil($data->totalResults / $resultsPage);
            if ($data->totalResults > 0) {
                $sqlOrder = "";
                switch($order) {
                    case "random":
                        $sqlOrder = " ORDER BY RANDOM() ";
                    break;
                    default:
                        $sqlOrder = " ORDER BY F.base_path ";
                    break;
                }
                $query = sprintf('
                    SELECT
                        DISTINCT F.base_path AS path, TMP_PT.totalTracks
                    FROM FILE F
                    LEFT JOIN (
                        SELECT
                            F.base_path, COUNT(*) AS totalTracks
                        FROM FILE F
                        GROUP BY F.base_path
                    ) TMP_PT ON TMP_PT.base_path = F.base_path
                    %s
                    %s
                    LIMIT %d OFFSET %d
                    ',
                    $whereCondition,
                    $sqlOrder,
                    $resultsPage,
                    $resultsPage * ($page -1)
                );
                $data->results = $dbh->query($query, $params);
            } else {
                $data->results = array();
            }
            return($data);
        }

    }

?>
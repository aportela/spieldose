<?php
    declare(strict_types=1);

    namespace Spieldose;

    class Path {
	    public function __construct () { }

        public function __destruct() { }

        public static function getPaths(\Spieldose\Database\DB $dbh, $filter = array()) {
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
            $query = '
                SELECT
                    DISTINCT F.base_path AS path, TMP_PT.totalTracks
                FROM FILE F
                LEFT JOIN (
                    SELECT
                        F.base_path, COUNT(*) AS totalTracks
                    FROM FILE F
                    GROUP BY F.base_path
                ) TMP_PT ON TMP_PT.base_path = F.base_path
                ' . $whereCondition . '
                ORDER BY F.base_path
            ';
            return($dbh->query($query, $params));
        }

    }

?>
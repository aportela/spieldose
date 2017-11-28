<?php
    declare(strict_types=1);

    namespace Spieldose;

    class Path {
	    public function __construct () { }

        public function __destruct() { }

        public static function getPaths(\Spieldose\Database\DB $dbh, $filter = array()) {
            $whereCondition = "";
            $params = array();
            if (isset($filter["path"]) && ! empty($filter["path"])) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":path", $filter["path"] . "\\%");
                $params[] = (new \Spieldose\Database\DBParam())->str(":currentPath", $filter["path"] . "\\");
                $whereCondition = ' WHERE F.base_path LIKE :path ';
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->str(":currentPath", "");
                $whereCondition = ' WHERE F.base_path NOT LIKE "%\%\%" ';
            }
            $query = '
                SELECT
                    DISTINCT REPLACE(F.base_path, :currentPath, "") AS path, TMP_PT.totalTracks
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
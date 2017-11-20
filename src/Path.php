<?php
    declare(strict_types=1);

    namespace Spieldose;

    class Path {
	    public function __construct () { }

        public function __destruct() { }

        public static function getPaths(\Spieldose\Database\DB $dbh) {
            $paths = array();
            if ($dbh == null) {
                $dbh = new \Spieldose\Database\DB();
            }
            $params = array();
            $query = '
                SELECT
                    DISTINCT F.base_path AS path
                FROM FILE F
                ORDER BY F.base_path
            ';
            foreach($dbh->query($query, $params) as $result) {
                $paths[] = $result->path;
            }
            return($paths);
        }

    }

?>
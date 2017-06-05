<?php

    namespace Spieldose;

    class Album
    {

	    public function __construct (string $short, array $long) { }

        public function __destruct() { }

        public static function search(\Spieldose\Database $dbh) {
            if ($dbh == null) {
                $dbh = new \Spieldose\Database();
            }
            return($dbh->query("SELECT DISTINCT album as name, artist, albumartist, year, images FROM FILE WHERE album IS NOT NULL GROUP BY COALESCE(albumartist, artist)"));
        }
    }

?>
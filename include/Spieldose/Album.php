<?php

    namespace Spieldose;

    class Album
    {

	    public function __construct () { }

        public function __destruct() { }

        public static function search(\Spieldose\Database $dbh, array $filter = array(), string $order = "") {
            if ($dbh == null) {
                $dbh = new \Spieldose\Database();
            }
            $sqlOrder = "";
            if (! empty($order)) {
                $sqlOrder = " ORDER BY RANDOM() ";
            } else {
                $sqlOrder = " ORDER BY FILE.album ASC, FILE.albumartist ASC, FILE.artist ASC ";
            }
            $query = sprintf(" SELECT DISTINCT album as name, artist, albumartist, year, images FROM FILE WHERE album IS NOT NULL GROUP BY COALESCE(albumartist, artist) %s ", $sqlOrder);
            return($dbh->query($query));
        }
    }

?>
<?php

    namespace Spieldose;

    class Artist
    {
        public $name;
        public $albums;

	    public function __construct (string $name = "", array $albums = array()) {
            $this->name = $name;
            $this->albums = $albums;
        }

        public function __destruct() { }

        private function exists(\Spieldose\Database $dbh): bool {
            return(true);
        }

        public function get(\Spieldose\Database $dbh) {
            if (isset($this->name) && ! empty($this->name)) {
                if ($dbh == null) {
                    $dbh = new \Spieldose\Database();
                }
                if ($this->exists($dbh)) {

                } else {
                    throw new \Spieldose\Exception\NotFoundException("name: " . $this->name);
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("name");
            }
        }

        public static function search(\Spieldose\Database $dbh, array $filter = array(), string $order = "") {
            if ($dbh == null) {
                $dbh = new \Spieldose\Database();
            }
            $sqlOrder = "";
            if (! empty($order)) {
                $sqlOrder = " ORDER BY RANDOM() ";
            } else {
                $sqlOrder = " ORDER BY FILE.artist ASC ";
            }
            $query = sprintf(" SELECT DISTINCT artist as name FROM FILE WHERE artist IS NOT NULL %s ", $sqlOrder);
            return($dbh->query($query));
        }
    }

?>
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

        public static function search(\Spieldose\Database $dbh, int $page = 1, int $resultsPage = 16, array $filter = array(), string $order = "") {
            if ($dbh == null) {
                $dbh = new \Spieldose\Database();
            }
            $queryCount = "SELECT COUNT(DISTINCT artist) AS total FROM FILE";
            $result = $dbh->query($queryCount);
            $data = new \stdClass();
            $data->actualPage = $page;
            $data->resultsPage = $resultsPage;
            $data->totalResults = $result[0]->total;
            $data->totalPages = ceil(($data->totalResults + $resultsPage - 1) / $resultsPage);
            $sqlOrder = "";
            if (! empty($order)) {
                $sqlOrder = " ORDER BY RANDOM() ";
            } else {
                $sqlOrder = " ORDER BY FILE.artist ASC ";
            }
            $query = sprintf(" SELECT DISTINCT artist as name FROM FILE WHERE artist IS NOT NULL %s LIMIT %d OFFSET %d", $sqlOrder, $resultsPage, $resultsPage * $page);
            $data->results = $dbh->query($query);
            return($data);
        }
    }

?>
<?php

    namespace Spieldose;

    class Track
    {

        public $id;
        public $path;

	    public function __construct (string $id = "") {
            $this->id = $id;
        }

        public function __destruct() { }

        private function exists(\Spieldose\Database $dbh): bool {
            return(true);
        }

        public function get(\Spieldose\Database $dbh) {
            if (isset($this->id) && ! empty($this->id)) {
                if ($dbh == null) {
                    $dbh = new \Spieldose\Database();
                }
                $results = $dbh->query("SELECT path FROM FILE WHERE id = :id", array(
                    (new \Spieldose\DatabaseParam())->str(":id", $this->id)
                ));
                if (count($results) == 1) {
                    $this->path = $results[0]->path;
                } else {
                    throw new \Spieldose\Exception\NotFoundException("id: " . $this->name);
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        public static function search(\Spieldose\Database $dbh, int $page = 1, int $resultsPage = 16, array $filter = array(), string $order = "") {
            if ($dbh == null) {
                $dbh = new \Spieldose\Database();
            }
            $queryCount = "SELECT COUNT(DISTINCT title) AS total FROM FILE";
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
                $sqlOrder = " ORDER BY FILE.title ASC, FILE.artist ASC, FILE.albumartist ASC ";
            }
            $query = sprintf(" SELECT DISTINCT id, title, artist, albumartist, year, playtime_seconds AS playtimeSeconds, playtime_string AS playtimeString, images FROM FILE WHERE album IS NOT NULL GROUP BY COALESCE(artist, albumartist) %s LIMIT %d OFFSET %d", $sqlOrder, $resultsPage, $resultsPage * $page);
            $data->results = $dbh->query($query);
            return($data);
        }

    }

?>
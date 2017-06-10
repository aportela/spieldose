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
                $results = $dbh->query("SELECT local_path AS path FROM FILE WHERE id = :id", array(
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
            $params = array();
            $whereCondition = "";
            if (isset($filter)) {
                $conditions = array();
                if (isset($filter["text"])) {
                    $conditions[] = " AND COALESCE(MBT.track, F.track_name) LIKE :text ";
                    $params[] = (new \Spieldose\DatabaseParam())->str(":text", "%" . $filter["text"] . "%");
                }
                if (isset($filter["artist"])) {
                    $conditions[] = " COALESCE(MBA2.artist, F.track_artist) = :artist ";
                    $params[] = (new \Spieldose\DatabaseParam())->str(":artist", $filter["artist"]);
                }
                if (isset($filter["album"])) {
                    $conditions[] = " COALESCE(MBA1.album, F.album_name) = :album ";
                    $params[] = (new \Spieldose\DatabaseParam())->str(":album", $filter["album"]);
                }
                $whereCondition = count($conditions) > 0 ? " AND " .  implode(" AND ", $conditions) : "";
            }
            $queryCount = '
                SELECT
                    COUNT (DISTINCT(COALESCE(MBT.track, F.track_name))) AS total
                FROM FILE F
                LEFT JOIN MB_CACHE_TRACK MBT ON MBT.mbid = F.track_mbid
                LEFT JOIN MB_CACHE_ALBUM MBA1 ON MBA1.mbid = F.album_mbid
                LEFT JOIN MB_CACHE_ARTIST MBA2 ON MBA2.mbid = F.artist_mbid
                WHERE COALESCE(MBT.track, F.track_name) IS NOT NULL
                ' . $whereCondition . '
            ';
            $result = $dbh->query($queryCount);
            $data = new \stdClass();
            $data->actualPage = $page;
            $data->resultsPage = $resultsPage;
            $data->totalResults = $result[0]->total;
            $data->totalPages = ceil($data->totalResults / $resultsPage);
            $sqlOrder = "";
            if (empty($order) || $order == "random") {
                $sqlOrder = " ORDER BY RANDOM() ";
            } else {
                $sqlOrder = " ORDER BY F.track_number, COALESCE(MBT.track, F.track_name) COLLATE NOCASE ASC ";
            }
            $query = sprintf('
                SELECT DISTINCT
                    id,
                    COALESCE(MBT.track, F.track_name) AS title,
                    COALESCE(MBA2.artist, F.track_artist) AS artist,
                    COALESCE(MBA1.album, F.album_name) AS album,
                    album_artist AS albumartist,
                    COALESCE(MBA1.year, F.year) AS year,
                    playtime_seconds AS playtimeSeconds,
                    playtime_string AS playtimeString,
                    COALESCE(MBA1.image, MBA2.image) AS image
                FROM FILE F
                LEFT JOIN MB_CACHE_TRACK MBT ON MBT.mbid = F.track_mbid
                LEFT JOIN MB_CACHE_ALBUM MBA1 ON MBA1.mbid = F.album_mbid
                LEFT JOIN MB_CACHE_ARTIST MBA2 ON MBA2.mbid = F.artist_mbid
                WHERE F.track_name IS NOT NULL
                %s
                %s
                LIMIT %d OFFSET %d
                ',
                $whereCondition,
                $sqlOrder,
                $resultsPage,
                $resultsPage * ($page - 1)
            );
            $data->results = $dbh->query($query, $params);
            return($data);
        }

    }

?>
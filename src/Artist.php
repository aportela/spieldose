<?php

    declare(strict_types=1);

    namespace Spieldose;

    class Artist {
        public $mbid;
        public $name;
        public $bio;
        public $albums;
        public $totalListeners;
        public $playCount;
        public $topTracks;
        public $lastFM;
        public $musicBrainz;
        public $similarArtists;

	    public function __construct (string $name = "", array $albums = array()) {
            $this->name = $name;
            $this->albums = $albums;
            $this->totalListeners = 0;
            $this->playCount = 0;
        }

        public function __destruct() { }

        /**
         * get artist metadata (bio, play stats & albums)
         *
         * @param \Spieldose\Database\DB $dbh database handler
         * name must be set
         */
        public function get(\Spieldose\Database\DB $dbh) {
            $this->getMusicBrainzMetadata($dbh);
            if (! empty($this->name)) {
                $params = array(
                    (new \Spieldose\Database\DBParam())->str(":name", $this->name)
                );
                $query = '
                    SELECT
                        COUNT(DISTINCT(COALESCE(CA.artist, F.track_artist))) AS total
                    FROM FILE F
                    LEFT JOIN CACHE_ARTIST CA ON CA.mbid = F.artist_mbid
                    WHERE COALESCE(CA.artist, F.track_artist) LIKE :name
                ';
                $data = $dbh->query($query, $params);
                if ($data && $data[0]->total > 0) {
                    $query = '
                        SELECT
                            COUNT(DISTINCT S.user_id) AS totalListeners
                        FROM STATS S
                        LEFT JOIN FILE F ON (F.id = S.file_id)
                        LEFT JOIN CACHE_ARTIST CA ON CA.mbid = F.artist_mbid
                        AND COALESCE(CA.artist, F.track_artist) LIKE :name
                    ';
                    $data = $dbh->query($query, $params);
                    if ($data && $data[0]->totalListeners) {
                        $this->totalListeners = intval($data[0]->totalListeners);
                    }
                    $query = '
                        SELECT
                            COUNT(S.played) AS playCount
                        FROM STATS S
                        LEFT JOIN FILE F ON (F.id = S.file_id)
                        LEFT JOIN CACHE_ARTIST CA ON CA.mbid = F.artist_mbid
                        AND COALESCE(CA.artist, F.track_artist) LIKE :name
                    ';
                    $data = $dbh->query($query, $params);
                    if ($data && $data[0]->playCount) {
                        $this->playCount = intval($data[0]->playCount);
                    }
                    $this->getMusicBrainzMetadata($dbh);
                    $this->getSimilarArtists(($dbh));
                    $this->topTracks = \Spieldose\Metrics::GetTopPlayedTracks($dbh, array("artist" => $this->name), 10);
                    $this->albums = (\Spieldose\Album::search($dbh, 1, 1024, array("artist" => $this->name), "year"))->results;
                } else {
                    throw new \Spieldose\Exception\NotFoundException("");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("name");
            }
        }

        /**
         * get artist musicbrainz metadata (bio & image)
         *
         * @param \Spieldose\Database\DB $dbh database handler
         */
        private function getMusicBrainzMetadata(\Spieldose\Database\DB $dbh) {
            if (! empty($this->name)) {
                $params = array(
                    (new \Spieldose\Database\DBParam())->str(":name", $this->name)
                );
                $query = sprintf('
                    SELECT DISTINCT
                        CA.mbid,
                        CA.image,
                        CA.lastfm_json,
                        CA.musicbrainz_json
                    FROM FILE F
                    LEFT JOIN CACHE_ARTIST CA ON CA.mbid = F.artist_mbid
                    WHERE COALESCE(CA.artist, F.track_artist) LIKE :name
                    AND CA.mbid IS NOT NULL
                ');
                $data = $dbh->query($query, $params);
                if ($data) {
                    $this->mbid = $data[0]->mbid;
                    $this->image = $data[0]->image;
                    $this->lastFM = ! empty($data[0]->lastfm_json) ? json_decode($data[0]->lastfm_json): null;

                    $this->musicBrainz = ! empty($data[0]->musicbrainz_json) ? json_decode($data[0]->musicbrainz_json): null;
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("name");
            }
        }

        /**
         * get artist similar relations with another artists (requires lastFM scrapped data)
         *
         * @param \Spieldose\Database\DB $dbh database handler
         */
        private function getSimilarArtists(\Spieldose\Database\DB $dbh) {
            $this->similarArtists = array();
            if (isset($this->lastFM) && isset($this->lastFM->artist) && isset($this->lastFM->artist->similar) && isset($this->lastFM->artist->similar->artist) && is_array($this->lastFM->artist->similar->artist)) {
                foreach($this->lastFM->artist->similar->artist as $similarArtist) {
                    if (! empty($similarArtist->name)) {
                        $params = array(
                            (new \Spieldose\Database\DBParam())->str(":name", $similarArtist->name)
                        );
                        $query = sprintf('
                            SELECT DISTINCT
                                CA.image
                            FROM FILE F
                            LEFT JOIN CACHE_ARTIST CA ON CA.mbid = F.artist_mbid
                            WHERE COALESCE(CA.artist, F.track_artist) LIKE :name
                        ');
                        $data = $dbh->query($query, $params);
                        if ($data) {
                            $artist = new \Spieldose\Artist();
                            $artist->name = $similarArtist->name;
                            $artist->image = $data[0]->image;
                            $this->similarArtists[] = $artist;
                        }
                    }
                }
            }
        }

        /**
         * ovewrite artist music brainz id
         *
         * @param \Spieldose\Database\DB $dbh database handler
         * @param string $name artist name
         * @param string $mbid artist music brainz id
         */
        public static function overwriteMusicBrainz(\Spieldose\Database\DB $dbh, string $name, string $mbid) {
            $mbArtist = \Spieldose\MusicBrainz\Artist::getFromMBId($mbid);
            if (! empty($mbArtist->mbId)) {
                $mbArtist->save($dbh);
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":artist_mbid", $mbArtist->mbId);
                $params[] = (new \Spieldose\Database\DBParam())->str(":track_artist", $name);
                $dbh->execute(" UPDATE FILE SET artist_mbid = :artist_mbid WHERE track_artist = :track_artist ", $params);
            }
        }

        /**
         * clear artist music brainz id
         *
         * @param \Spieldose\Database\DB $dbh database handler
         * @param string $name artist name
         */
        public static function clearMusicBrainz(\Spieldose\Database\DB $dbh, string $name) {
            $params = array();
            $params[] = (new \Spieldose\Database\DBParam())->str(":name", $name);
            $dbh->execute(" UPDATE FILE SET artist_mbid = NULL WHERE EXISTS (SELECT CA.mbid FROM MB_CACHE_ARTIST MBA WHERE CA.artist LIKE :name AND CA.mbid = FILE.artist_mbid ) ", $params);
        }

        /**
         * search artists
         *
         * @param \Spieldose\Database\DB $dbh database handler
         * @param int $page return results from this page
         * @param int $resultsPage number of results / page
         * @param array $filter the condition filter
         * @param string $order results order
         */
        public static function search(\Spieldose\Database\DB $dbh, int $page = 1, int $resultsPage = 1024, array $filter = array(), string $order = "") {
            $params = array();
            $whereCondition = "";
            if (isset($filter)) {
                $conditions = array();
                if (isset($filter["withoutMbid"]) && $filter["withoutMbid"]) {
                    $conditions[] = " CA.mbid IS NULL ";
                }
                if (isset($filter["partialName"]) && ! empty($filter["partialName"])) {
                    $conditions[] = " COALESCE(CA.artist, F.track_artist) LIKE :partialName ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":partialName", "%" . $filter["partialName"] . "%");
                }
                if (isset($filter["name"]) && ! empty($filter["name"])) {
                    $conditions[] = " COALESCE(CA.artist, F.track_artist) LIKE :name ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":name", $filter["name"]);
                }
                $whereCondition = count($conditions) > 0 ? " AND " .  implode(" AND ", $conditions) : "";
            }
            $queryCount = '
                SELECT
                    COUNT (DISTINCT(COALESCE(CA.artist, F.track_artist))) AS total
                FROM FILE F
                LEFT JOIN CACHE_ARTIST CA ON CA.mbid = F.artist_mbid
                WHERE COALESCE(CA.artist, F.track_artist) IS NOT NULL
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
                        $sqlOrder = " ORDER BY COALESCE(CA.artist, F.track_artist) COLLATE NOCASE ASC ";
                    break;
                }
                $query = sprintf('
                    SELECT
                        DISTINCT COALESCE(CA.artist, F.track_artist) as name,
                        CA.image
                    FROM FILE F
                    LEFT JOIN CACHE_ARTIST CA ON CA.mbid = F.artist_mbid
                    WHERE COALESCE(CA.artist, F.track_artist) IS NOT NULL
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
            } else {
                $data->results = array();
            }
            return($data);
        }

    }

?>
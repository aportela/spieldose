<?php

    declare(strict_types=1);

    namespace Spieldose;

    class Artist {
        public $mbid;
        public $name;
        public $bio;
        public $albums;
        public $playCount;

	    public function __construct (string $name = "", array $albums = array()) {
            $this->name = $name;
            $this->albums = $albums;
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
                        COUNT(DISTINCT(COALESCE(MBA.artist, F.track_artist))) AS total
                    FROM FILE F
                    LEFT JOIN MB_CACHE_ARTIST MBA ON MBA.mbid = F.artist_mbid
                    WHERE COALESCE(MBA.artist, F.track_artist) LIKE :name
                ';
                $data = $dbh->query($query, $params);
                if ($data && $data[0]->total > 0) {
                    $params[] = (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId());
                    $query = '
                        SELECT
                            COUNT(S.played) AS playCount
                        FROM STATS S
                        LEFT JOIN FILE F ON (F.id = S.file_id)
                        LEFT JOIN MB_CACHE_ARTIST MBA ON MBA.mbid = F.artist_mbid
                        WHERE S.user_id = :user_id
                        AND COALESCE(MBA.artist, F.track_artist) LIKE :name
                    ';
                    $data = $dbh->query($query, $params);
                    if ($data && $data[0]->playCount) {
                        $this->playCount = $data[0]->playCount;
                    }
                    $this->getMusicBrainzMetadata($dbh);
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
                        MBA.mbid,
                        MBA.bio,
                        MBA.image
                    FROM FILE F
                    LEFT JOIN MB_CACHE_ARTIST MBA ON MBA.mbid = F.artist_mbid
                    WHERE COALESCE(MBA.artist, F.track_artist) LIKE :name
                    AND MBA.mbid IS NOT NULL
                ');
                $data = $dbh->query($query, $params);
                if ($data) {
                    $this->mbid = $data[0]->mbid;
                    $this->bio = $data[0]->bio;
                    $this->image = $data[0]->image;
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("name");
            }
        }

        /**
         * ovewrite artist music brainz id
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
                if (isset($filter["partialName"]) && ! empty($filter["partialName"])) {
                    $conditions[] = " COALESCE(MBA.artist, F.track_artist) LIKE :partialName ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":partialName", "%" . $filter["partialName"] . "%");
                }
                if (isset($filter["name"]) && ! empty($filter["name"])) {
                    $conditions[] = " COALESCE(MBA.artist, F.track_artist) LIKE :name ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":name", $filter["name"]);
                }
                $whereCondition = count($conditions) > 0 ? " AND " .  implode(" AND ", $conditions) : "";
            }
            $queryCount = '
                SELECT
                    COUNT (DISTINCT(COALESCE(MBA.artist, F.track_artist))) AS total
                FROM FILE F
                LEFT JOIN MB_CACHE_ARTIST MBA ON MBA.mbid = F.artist_mbid
                WHERE COALESCE(MBA.artist, F.track_artist) IS NOT NULL
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
                        $sqlOrder = " ORDER BY COALESCE(MBA.artist, F.track_artist) COLLATE NOCASE ASC ";
                    break;
                }
                $query = sprintf('
                    SELECT
                        DISTINCT COALESCE(MBA.artist, F.track_artist) as name,
                        MBA.image
                    FROM FILE F
                    LEFT JOIN MB_CACHE_ARTIST MBA ON MBA.mbid = F.artist_mbid
                    WHERE COALESCE(MBA.artist, F.track_artist) IS NOT NULL
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
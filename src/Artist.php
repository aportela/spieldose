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
        }

        public function __destruct() { }

        /**
         * get artist metadata (bio & albums)
         *
         * @param \Spieldose\Database\DB $dbh database handler
         */
        public function get(\Spieldose\Database\DB $dbh) {
            if (! empty($this->name)) {
                $params = array(
                    (new \Spieldose\Database\DBParam())->str(":name", $this->name),
                    (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
                );
                $query = sprintf('
                    SELECT DISTINCT
                        MBA.mbid,
                        MBA.bio,
                        MBA.image,
                        COUNT(*) AS playCount
                    FROM FILE F
                    LEFT JOIN MB_CACHE_ARTIST MBA ON MBA.mbid = F.artist_mbid
                    LEFT JOIN STATS S ON (S.file_id = F.id AND S.user_id = :user_id)
                    WHERE COALESCE(MBA.artist, F.track_artist) = :name
                ');
                $data = $dbh->query($query, $params);
                if ($data) {
                    $this->mbid = $data[0]->mbid;
                    $this->bio = $data[0]->bio;
                    $this->image = $data[0]->image;
                    $this->playCount = $data[0]->playCount;
                    $this->albums = (\Spieldose\Album::search($dbh, 1, 16, array("artist" => $this->name), ""))->results;
                } else {
                    throw new \Spieldose\Exception\NotFoundException("name: " . $this->name);
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("name");
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
                if (isset($filter["partialName"]) && ! empty($filter["partialName"])) {
                    $whereCondition = " AND COALESCE(MBA.artist, F.track_artist) LIKE :partialName ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":partialName", "%" . $filter["partialName"] . "%");
                }
                if (isset($filter["name"]) && ! empty($filter["name"])) {
                    $whereCondition = " AND COALESCE(MBA.artist, F.track_artist) LIKE :name ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":name", $filter["name"]);
                }
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
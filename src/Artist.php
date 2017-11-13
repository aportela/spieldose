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

        private function exists(\Spieldose\Database\DB $dbh): bool {
            return(true);
        }

        public function get(\Spieldose\Database\DB $dbh) {
            if (isset($this->name) && ! empty($this->name)) {
                if ($dbh == null) {
                    $dbh = new \Spieldose\Database\DB();
                }
                if ($this->exists($dbh)) {
                    $params = array();
                    $params[] = (new \Spieldose\Database\DBParam())->str(":name", $this->name);
                    $query = sprintf('
                        SELECT DISTINCT
                            MBA.mbid,
                            MBA.bio,
                            MBA.image,
                            COUNT(*) AS playCount
                        FROM FILE F
                        LEFT JOIN MB_CACHE_ARTIST MBA ON MBA.mbid = F.artist_mbid
                        LEFT JOIN STATS S ON S.file_id = F.id
                        WHERE COALESCE(MBA.artist, F.track_artist) = :name
                        GROUP BY (MBA.mbid)
                        '
                    );
                    $data = $dbh->query($query, $params);
                    if ($data) {
                        $this->mbid = $data[0]->mbid;
                        $this->bio = $data[0]->bio;
                        $this->image = $data[0]->image;
                        $this->playCount = $data[0]->playCount;
                    }
                    $this->getAlbums($dbh);
                    $totalAlbums = count($this->albums);
                    for ($i = 0; $i < $totalAlbums; $i++) {
                        $this->albums[$i]->tracks = \Spieldose\Album::getTracks($dbh, $this->albums[$i]->name, $this->name);
                    }
                } else {
                    throw new \Spieldose\Exception\NotFoundException("name: " . $this->name);
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("name");
            }
        }

        private function getAlbums(\Spieldose\Database\DB $dbh) {
            if (isset($this->name) && ! empty($this->name)) {
                if ($dbh == null) {
                    $dbh = new \Spieldose\Database\DB();
                }
                if ($this->exists($dbh)) {
                    $params = array();
                    $params[] = (new \Spieldose\Database\DBParam())->str(":name", $this->name);
                    $query = sprintf('
                        SELECT DISTINCT
                            MBA2.mbid,
                            COALESCE(MBA2.album, F.album_name) as name,
                            COALESCE(MBA2.year, F.year) as year,
                            MBA2.image
                        FROM FILE F
                        LEFT JOIN MB_CACHE_ARTIST MBA1 ON MBA1.mbid = F.artist_mbid
                        LEFT JOIN MB_CACHE_ALBUM MBA2 ON MBA2.mbid = F.album_mbid
                        WHERE COALESCE(MBA1.artist, F.track_artist) = :name
                        ORDER BY COALESCE(MBA2.year, F.year)
                        '
                    );
                    $this->albums = $dbh->query($query, $params);
                } else {
                    throw new \Spieldose\Exception\NotFoundException("name: " . $this->name);
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("name");
            }
        }

        public static function search(\Spieldose\Database\DB $dbh, int $page = 1, int $resultsPage = 16, array $filter = array(), string $order = "") {
            if ($dbh == null) {
                $dbh = new \Spieldose\Database\DB();
            }
            $params = array();
            $whereCondition = "";
            if (isset($filter)) {
                if (isset($filter["text"]) && ! empty($filter["text"])) {
                    $whereCondition = " AND COALESCE(MBA.artist, F.track_artist) LIKE :text ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":text", "%" . $filter["text"] . "%");
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
            $sqlOrder = "";
            if (! empty($order) && $order == "random") {
                $sqlOrder = " ORDER BY RANDOM() ";
            } else {
                $sqlOrder = " ORDER BY COALESCE(MBA.artist, F.track_artist) COLLATE NOCASE ASC ";
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
            return($data);
        }
    }

?>
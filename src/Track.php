<?php

    declare(strict_types=1);

    namespace Spieldose;

    class Track {

        public $id;
        public $path;
        public $mime;

	    public function __construct (string $id = "") {
            $this->id = $id;
        }

        public function __destruct() { }

        private function exists(\Spieldose\Database\DB $dbh): bool {
            return(true);
        }

        public function get(\Spieldose\Database\DB $dbh) {
            if (isset($this->id) && ! empty($this->id)) {
                $results = $dbh->query("SELECT local_path AS path, mime FROM FILE WHERE id = :id", array(
                    (new \Spieldose\Database\DBParam())->str(":id", $this->id)
                ));
                if (count($results) == 1) {
                    $this->path = $results[0]->path;
                    $this->mime = $results[0]->mime;
                } else {
                    throw new \Spieldose\Exception\NotFoundException("id: " . $this->name);
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        public static function search(\Spieldose\Database\DB $dbh, int $page = 1, int $resultsPage = 16, array $filter = array(), string $order = "") {
            $params = array();
            $whereCondition = "";
            if (isset($filter)) {
                $conditions = array();
                if (isset($filter["text"]) && ! empty($filter["text"])) {
                    $conditions[] = " ( COALESCE(MBT.track, F.track_name) LIKE :text OR COALESCE(MBA2.artist, F.track_artist) LIKE :text OR COALESCE(MBA1.album, F.album_name) LIKE :text ) ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":text", "%" . $filter["text"] . "%");
                }
                if (isset($filter["artist"]) && ! empty($filter["artist"])) {
                    $conditions[] = " ( MBA1.artist = :artist OR MBA2.artist = :artist OR F.track_artist = :artist OR F.album_artist = :artist ) ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":artist", $filter["artist"]);
                }
                if (isset($filter["album"]) && ! empty($filter["album"])) {
                    $conditions[] = " ( MBA1.album = :album OR F.album_name = :album ) ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":album", $filter["album"]);
                }
                if (isset($filter["year"]) && ! empty($filter["year"])) {
                    $conditions[] = " ( MBA1.year = :year OR F.year = :year ) ";
                    $params[] = (new \Spieldose\Database\DBParam())->int(":year", intval($filter["year"]));
                }
                if (isset($filter["playlist"]) && ! empty($filter["playlist"])) {
                    // TODO: check permissions
                    $conditions[] = " EXISTS ( SELECT * FROM PLAYLIST_TRACK WHERE file_id = F.id AND playlist_id = :playlist_id ) ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":playlist_id", $filter["playlist"]);
                }
                if (isset($filter["path"]) && ! empty($filter["path"])) {
                    $conditions[] = " ( F.base_path = :path ) ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":path", $filter["path"]);
                }
                $whereCondition = count($conditions) > 0 ? " AND " .  implode(" AND ", $conditions) : "";
            }
            $queryCount = '
                SELECT
                    COUNT (F.id) AS total
                FROM FILE F
                LEFT JOIN MB_CACHE_TRACK MBT ON MBT.mbid = F.track_mbid
                LEFT JOIN MB_CACHE_ALBUM MBA1 ON MBA1.mbid = F.album_mbid
                LEFT JOIN MB_CACHE_ARTIST MBA2 ON MBA2.mbid = F.artist_mbid
                WHERE COALESCE(MBT.track, F.track_name) IS NOT NULL
                ' . $whereCondition . '
            ';
            $result = $dbh->query($queryCount, $params);
            $data = new \stdClass();
            $data->actualPage = $page;
            $data->resultsPage = $resultsPage;
            $data->totalResults = $result[0]->total;
            if ($resultsPage > 0) {
                $data->totalPages = ceil($data->totalResults / $resultsPage);
            } else {
                $data->totalPages = $data->totalResults > 0 ? 1: 0;
                $resultsPage = $data->totalResults;
            }
            $sqlOrder = "";
            if (! empty($order) && $order == "random") {
                $sqlOrder = " ORDER BY RANDOM() ";
            } else {
                if (isset($filter["artist"]) && ! empty($filter["artist"])) {
                    $sqlOrder = " ORDER BY year ASC, album COLLATE NOCASE ASC, F.track_number ";
                } else {
                    $sqlOrder = " ORDER BY F.track_number, COALESCE(MBT.track, F.track_name) COLLATE NOCASE ASC ";
                }
            }
            $params[] = (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId());
            $query = sprintf('
                SELECT DISTINCT
                    id,
                    F.track_number AS number,
                    COALESCE(MBT.track, F.track_name) AS title,
                    COALESCE(MBA2.artist, F.track_artist) AS artist,
                    COALESCE(MBA1.album, F.album_name) AS album,
                    album_artist AS albumartist,
                    COALESCE(MBA1.year, F.year) AS year,
                    playtime_seconds AS playtimeSeconds,
                    playtime_string AS playtimeString,
                    MBA1.image AS image,
                    genre,
                    mime,
                    COALESCE(LF.loved, 0) AS loved
                FROM FILE F
                LEFT JOIN MB_CACHE_TRACK MBT ON MBT.mbid = F.track_mbid
                LEFT JOIN MB_CACHE_ALBUM MBA1 ON MBA1.mbid = F.album_mbid
                LEFT JOIN MB_CACHE_ARTIST MBA2 ON MBA2.mbid = F.artist_mbid
                LEFT JOIN LOVED_FILE LF ON (LF.file_id = F.id AND LF.user_id = :user_id)
                WHERE COALESCE(MBT.track, F.track_name) IS NOT NULL
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

        public function incPlayCount(\Spieldose\Database\DB $dbh): bool {
            if (isset($this->id) && ! empty($this->id)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":file_id", $this->id);
                $params[] = (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId());
                return($dbh->execute('INSERT INTO STATS (user_id, file_id, played) VALUES(:user_id, :file_id, CURRENT_TIMESTAMP); ', $params));
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        public function love(\Spieldose\Database\DB $dbh): bool {
            if (isset($this->id) && ! empty($this->id)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":file_id", $this->id);
                $params[] = (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId());
                return($dbh->execute('REPLACE INTO LOVED_FILE (file_id, user_id, loved) VALUES(:file_id, :user_id, 1); ', $params));
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        public function unLove(\Spieldose\Database\DB $dbh): bool {
            if (isset($this->id) && ! empty($this->id)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":file_id", $this->id);
                $params[] = (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId());
                return($dbh->execute('DELETE FROM LOVED_FILE WHERE file_id = :file_id AND user_id = :user_id; ', $params));
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }
    }

?>
<?php
    declare(strict_types=1);

    namespace Spieldose;

    class Playlist {

        public $id;
        public $name;
        public $tracks;

	    public function __construct (string $id = "", string $name = "", array $tracks = array()) {
            $this->id = $id;
            $this->name = $name;
            $this->tracks = $tracks;
        }

        public function __destruct() { }

        public function isAllowed(\Spieldose\Database\DB $dbh) {
            if (isset($this->id) && ! empty($this->id)) {
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":id", $this->id);
                $query = sprintf('
                    SELECT P.user_id AS userId
                    FROM PLAYLIST P
                    WHERE P.id = :id
                    '
                );
                $data = $dbh->query($query, $params);
                if (count($data) == 1) {
                    return($data[0]->userId == \Spieldose\User::getUserId());
                } else {
                    throw new \Spieldose\Exception\NotFoundException("id: " . $this->id);
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        public function add(\Spieldose\Database\DB $dbh) {
            if (! empty($this->id)) {
                if (! empty($this->name)) {
                    $params = array(
                        (new \Spieldose\Database\DBParam())->str(":id", $this->id),
                        (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId()),
                        (new \Spieldose\Database\DBParam())->str(":name", $this->name)
                    );
                    if ($dbh->execute(" INSERT INTO PLAYLIST (id, user_id, name) VALUES(:id, :user_id, :name) ", $params)) {
                        foreach($this->tracks as $trackId) {
                            $params = array(
                                (new \Spieldose\Database\DBParam())->str(":playlist_id", $this->id),
                                (new \Spieldose\Database\DBParam())->str(":file_id", $trackId)
                            );
                            if (! $dbh->execute(" INSERT INTO PLAYLIST_TRACK (playlist_id, file_id) VALUES(:playlist_id, :file_id) ", $params)) {
                                throw new \PDOException("");
                            }
                        }
                    } else {
                        throw new \PDOException("");
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("name");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        public function update(\Spieldose\Database\DB $dbh) {
            if (! empty($this->id)) {
                if (! empty($this->name)) {
                    $params = array(
                        (new \Spieldose\Database\DBParam())->str(":id", $this->id),
                        (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId()),
                        (new \Spieldose\Database\DBParam())->str(":name", $this->name)
                    );
                    if ($dbh->execute(" UPDATE PLAYLIST SET name = :name WHERE id = :id AND user_id = :user_id ", $params)) {
                        $params = array(
                            (new \Spieldose\Database\DBParam())->str(":playlist_id", $this->id),
                        );
                        $dbh->execute(" DELETE FROM PLAYLIST_TRACK WHERE playlist_id = :playlist_id ", $params);
                        foreach($this->tracks as $trackId) {
                            $params = array(
                                (new \Spieldose\Database\DBParam())->str(":playlist_id", $this->id),
                            );
                            $params = array(
                                (new \Spieldose\Database\DBParam())->str(":playlist_id", $this->id),
                                (new \Spieldose\Database\DBParam())->str(":file_id", $trackId)
                            );
                            if (! $dbh->execute(" INSERT INTO PLAYLIST_TRACK (playlist_id, file_id) VALUES(:playlist_id, :file_id) ", $params)) {
                                throw new \PDOException("");
                            }
                        }
                    } else {
                        throw new \PDOException("");
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("name");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        public function remove(\Spieldose\Database\DB $dbh) {
            if (! empty($this->id)) {
                $params = array(
                    (new \Spieldose\Database\DBParam())->str(":playlist_id", $this->id),
                );
                $dbh->execute(" DELETE FROM PLAYLIST_TRACK WHERE playlist_id = :playlist_id ", $params);
                $params = array(
                    (new \Spieldose\Database\DBParam())->str(":id", $this->id),
                    (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId()),
                );
                $dbh->execute(" DELETE FROM PLAYLIST WHERE id = :id AND user_id = :user_id ", $params);
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        public function get(\Spieldose\Database\DB $dbh) {
            if (! empty($this->id)) {
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":id", $this->id);
                $query = sprintf('
                    SELECT P.id, P.name
                    FROM PLAYLIST P
                    WHERE P.id = :id
                    '
                );
                $data = $dbh->query($query, $params);
                if (count($data) == 1) {
                    $this->id = $data[0]->id;
                    $this->name = $data[0]->name;
                    $this->tracks = $this->getPlaylistTracks($dbh);
                } else {
                    throw new \Spieldose\Exception\NotFoundException("");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        private function getPlaylistTracks(\Spieldose\Database\DB $dbh) {
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId()),
                (new \Spieldose\Database\DBParam())->str(":playlist_id", $this->id)
            );
            $whereCondition = " AND PT.playlist_id = :playlist_id ";
            $sqlOrder = "";
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
                INNER JOIN PLAYLIST_TRACK PT ON PT.file_id = F.id
                LEFT JOIN MB_CACHE_TRACK MBT ON MBT.mbid = F.track_mbid
                LEFT JOIN MB_CACHE_ALBUM MBA1 ON MBA1.mbid = F.album_mbid
                LEFT JOIN MB_CACHE_ARTIST MBA2 ON MBA2.mbid = F.artist_mbid
                LEFT JOIN LOVED_FILE LF ON (LF.file_id = F.id AND LF.user_id = :user_id)
                WHERE COALESCE(MBT.track, F.track_name) IS NOT NULL
                %s
                %s
                ',
                $whereCondition,
                $sqlOrder
            );
            return($dbh->query($query, $params));
        }

        public static function search(\Spieldose\Database\DB $dbh, int $page = 1, int $resultsPage = 16, array $filter = array(), string $order = "") {
            $params = array();
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
            );
            $queryConditions = array(
                " P.user_id = :user_id "
            );
            $whereCondition = "";
            if (isset($filter)) {
                if (isset($filter["partialName"]) && ! empty($filter["partialName"])) {
                    $queryConditions[] = " P.name LIKE :partialName ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":partialName", "%". $filter["partialName"] . "%");
                }
                if (isset($filter["name"]) && ! empty($filter["name"])) {
                    $queryConditions[] = " P.name LIKE :name ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":name", $filter["name"]);
                }
            }
            $whereCondition = count($queryConditions) > 0 ? " WHERE " .  implode(" AND ", $queryConditions) : "";
            $queryCount = sprintf('
                SELECT SUM(TMP.total) AS total
                FROM (
                    SELECT
                        COUNT (P.id) AS total
                    FROM PLAYLIST P
                    %s
                    UNION ALL
                    SELECT COUNT(DISTINCT user_id) AS total
                    FROM LOVED_FILE WHERE user_id = :user_id
                ) TMP
            ', $whereCondition);
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
                        $sqlOrder = " ORDER BY name COLLATE NOCASE ASC ";
                    break;
                }
                $query = sprintf('
                    SELECT id, name, total AS trackCount
                    FROM (
                        SELECT P.id, P.name, TMP_COUNT.total
                        FROM PLAYLIST P
                        LEFT JOIN (
                            SELECT COUNT(file_id) AS total, playlist_id
                            FROM PLAYLIST_TRACK
                            GROUP BY playlist_id
                        ) TMP_COUNT ON TMP_COUNT.playlist_id = P.id
                        %s
                        UNION ALL
                        SELECT NULL AS id, "My loved tracks" AS name, COUNT(*) AS total
                        FROM LOVED_FILE WHERE user_id = :user_id
                        GROUP BY user_id
                    ) TMP
                    %s
                    LIMIT %d OFFSET %d
                    ', (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions): ''),
                    $sqlOrder,
                    $resultsPage,
                    $resultsPage * ($page -1)
                );
                $data->results = $dbh->query($query, $params);
            } else {
                $data->results = array();
            }
            return($data);
        }
    }

?>
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
                        foreach($this->tracks as $trackId) {
                            $params = array(
                                (new \Spieldose\Database\DBParam())->str(":playlist_id", $this->id),
                            );
                            $dbh->execute(" DELETE FROM PLAYLIST_TRACK WHERE playlist_id = :playlist_id ", $params);
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

        public function get(\Spieldose\Database\DB $dbh) {
            if (isset($this->id) && ! empty($this->id)) {
                if ($dbh == null) {
                    $dbh = new \Spieldose\Database\DB();
                }
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":id", $this->id);
                $query = sprintf('
                    SELECT P.name, P.user_id AS userId
                    FROM PLAYLIST P
                    WHERE P.id = :id
                    '
                );
                $data = $dbh->query($query, $params);
                if (count($data) == 1) {
                    if ($data[0]->userId == \Spieldose\User::getUserId()) {
                        $this->name = $data[0]->name;
                        $this->tracks = [];
                    } else {
                        throw new \Spieldose\Exception\AccessDeniedException("id: " . $this->id . "userId:" . $data[0]->userId);
                    }
                } else {
                    throw new \Spieldose\Exception\NotFoundException("id: " . $this->id);
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        public static function search(\Spieldose\Database\DB $dbh, int $page = 1, int $resultsPage = 16, array $filter = array(), string $order = "") {
            if ($dbh == null) {
                $dbh = new \Spieldose\Database\DB();
            }
            $params = array();
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
            );
            $queryConditions = array(
                " P.user_id = :user_id "
            );
            $whereCondition = "";
            if (isset($filter)) {
                if (isset($filter["text"]) && ! empty($filter["text"])) {
                    $queryConditions[] = " P.name LIKE :text ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":text", "%" . $filter["text"] . "%");
                }
            }
            $queryCount = sprintf('
                SELECT
                    COUNT (P.id) AS total
                FROM PLAYLIST P
                %s
            ', (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions): ''));
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
                $sqlOrder = " ORDER BY P.name COLLATE NOCASE ASC ";
            }
            $query = sprintf('
                SELECT P.id, P.name, TMP_COUNT.total AS trackCount
                FROM PLAYLIST P
                LEFT JOIN (
                    SELECT COUNT(file_id) AS total, playlist_id
                    FROM PLAYLIST_TRACK
                    GROUP BY playlist_id
                ) TMP_COUNT ON TMP_COUNT.playlist_id = P.id
                %s
                %s
                LIMIT %d OFFSET %d
                ', (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions): ''),
                $sqlOrder,
                $resultsPage,
                $resultsPage * ($page -1)
            );
            $data->results = $dbh->query($query, $params);
            return($data);
        }
    }

?>
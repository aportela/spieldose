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

        private function exists(\Spieldose\Database\DB $dbh): bool {
            return(true);
        }

        public function get(\Spieldose\Database\DB $dbh) {
            if (isset($this->id) && ! empty($this->id)) {
                if ($dbh == null) {
                    $dbh = new \Spieldose\Database\DB();
                }
                if ($this->exists($dbh)) {
                    $params = array();
                    $params[] = (new \Spieldose\Database\DBParam())->str(":id", $this->id);
                    $params[] = (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId());
                    $query = sprintf('
                        SELECT P.name
                        FROM PLAYLIST P
                        WHERE P.id = :id AND p.user_id = :user_id
                        '
                    );
                    $data = $dbh->query($query, $params);
                    $this->name = $data[0]->name;
                    $data = \Spieldose\Track::search($dbh, 1, 10, array("playlist" => $this->id), "");
                    $this->tracks = $data->results;
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
                SELECT P.id, P.name, 0 AS trackCount
                FROM PLAYLIST P
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
<?php
    declare(strict_types=1);

    namespace Spieldose;

    class RadioStation {

        public $id;
        public $name;
        public $url;
        public $streamUrls;
        public $urlType;
        public $image;

	    public function __construct (string $id = "", string $name = "", string $url = "", int $urlType = 0, string $image = "") {
            $this->id = $id;
            $this->name = $name;
            $this->url = $url;
            $this->urlType = $urlType;
            $this->image = $image;
            $this->streamUrls = array();
        }

        public function __destruct() { }

        public function isAllowed(\Spieldose\Database\DB $dbh) {
            if (isset($this->id) && ! empty($this->id)) {
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":id", $this->id);
                $query = sprintf('
                    SELECT RS.user_id AS userId
                    FROM RADIO_STATION RS
                    WHERE RS.id = :id
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
                    if (! empty($this->url) && filter_var($this->url, FILTER_VALIDATE_URL)) {
                        $params = array(
                            (new \Spieldose\Database\DBParam())->str(":id", $this->id),
                            (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId()),
                            (new \Spieldose\Database\DBParam())->str(":name", $this->name),
                            (new \Spieldose\Database\DBParam())->str(":url", $this->url),
                            (new \Spieldose\Database\DBParam())->int(":url_type", $this->urlType),
                            (new \Spieldose\Database\DBParam())->str(":image", $this->image)
                        );
                        return($dbh->execute(" INSERT INTO RADIO_STATION (id, user_id, name, url, url_type, image) VALUES(:id, :user_id, :name, :url, :url_type, :image) ", $params));
                    } else {
                        throw new \Spieldose\Exception\InvalidParamsException("url");
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
                    if (! empty($this->url) && filter_var($this->url, FILTER_VALIDATE_URL)) {
                        $params = array(
                            (new \Spieldose\Database\DBParam())->str(":id", $this->id),
                            (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId()),
                            (new \Spieldose\Database\DBParam())->str(":name", $this->name),
                            (new \Spieldose\Database\DBParam())->str(":url", $this->url),
                            (new \Spieldose\Database\DBParam())->int(":url_type", $this->urlType),
                            (new \Spieldose\Database\DBParam())->str(":image", $this->image)
                        );
                        return($dbh->execute(" UPDATE RADIO_STATION SET name = :name, url = :url, url_type = :url_type, image = :image WHERE id = :id AND user_id = :user_id ", $params));
                    } else {
                        throw new \Spieldose\Exception\InvalidParamsException("url");
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
                    (new \Spieldose\Database\DBParam())->str(":id", $this->id),
                    (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId()),
                );
                $dbh->execute(" DELETE FROM RADIO_STATION WHERE id = :id AND user_id = :user_id ", $params);
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        public function get(\Spieldose\Database\DB $dbh) {
            if (! empty($this->id)) {
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":id", $this->id);
                $query = sprintf('
                    SELECT RS.id, RS.name, RS.url, RS.url_type AS urlType, RS.image
                    FROM RADIO_STATION RS
                    WHERE RS.id = :id
                    '
                );
                $data = $dbh->query($query, $params);
                if (count($data) == 1) {
                    $this->id = $data[0]->id;
                    $this->name = $data[0]->name;
                    $this->url = $data[0]->url;
                    $this->urlType = $data[0]->urlType;
                    switch($this->urlType) {
                        case 0:
                            $this->streamUrls = array($this->url);
                        break;
                        case 1:
                            $this->streamUrls = $this->getStreamFromM3U($this->url);
                        break;
                        case 2:
                            $this->streamUrls = $this->getStreamFromPLS($this->url);
                        break;
                    }
                    $this->image = $data[0]->image;
                } else {
                    throw new \Spieldose\Exception\NotFoundException("");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        private function getStreamFromM3U($url) {
            $streamUrls = array();
            $data = \Spieldose\Net::httpRequest($url);
            if (! empty($data)) {
                $lines = explode("\n", $data);
                foreach($lines as $line) {
                    if (substr($data, 0, 4 ) === "http") {
                        array_push($streamUrls, trim($line));
                    }
                }
            }
            return($streamUrls);
        }

        private function getStreamFromPLS($url) {
            $streamUrls = array();
            $data = \Spieldose\Net::httpRequest($url);
            if (! empty($data)) {
                $parsedData = parse_ini_string ($data, true, INI_SCANNER_RAW);
                if (isset($parsedData["playlist"]) && is_array(($parsedData["playlist"]))) {
                    foreach($parsedData["playlist"] as $key => $value) {
                        if (substr($key, 0, 4 ) === "File" && substr($value, 0, 4 ) === "http") {
                            array_push($streamUrls, $value);
                        }
                    }
                }
            }
            return($streamUrls);
        }

        public static function search(\Spieldose\Database\DB $dbh, int $page = 1, int $resultsPage = 16, array $filter = array(), string $order = "") {
            $params = array();
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
            );
            $queryConditions = array(
                " RS.user_id = :user_id "
            );
            $whereCondition = "";
            if (isset($filter)) {
                if (isset($filter["partialName"]) && ! empty($filter["partialName"])) {
                    $queryConditions[] = " RS.name LIKE :partialName ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":partialName", "%". $filter["partialName"] . "%");
                }
                if (isset($filter["name"]) && ! empty($filter["name"])) {
                    $queryConditions[] = " RS.name LIKE :name ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":name", $filter["name"]);
                }
            }
            $whereCondition = count($queryConditions) > 0 ? " WHERE " .  implode(" AND ", $queryConditions) : "";
            $queryCount = sprintf('
                SELECT SUM(TMP.total) AS total
                FROM (
                    SELECT
                        COUNT (RS.id) AS total
                    FROM RADIO_STATION RS
                    %s
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
                    SELECT RS.id, RS.name, RS.image
                    FROM RADIO_STATION RS
                    %s
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
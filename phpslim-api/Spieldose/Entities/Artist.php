<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Artist extends \Spieldose\Entities\Entity
{

    public $name = null;
    public $image = null;
    public $relations = null;
    public $bio = null;

    public static function search(\aportela\DatabaseWrapper\DB $dbh, int $currentPage = 1, int $resultsPage = 32, $filter = array(), string $sortBy = "", string $sortOrder = ""): array
    {
        $params = array(
            new \aportela\DatabaseWrapper\Param\IntegerParam(":resultsPage", $resultsPage)
        );
        $filterConditions = array();
        if (isset($filter["name"]) && !empty($filter["name"])) {
            $filterConditions[] = " FIT.artist LIKE :name";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":name", "%" . $filter["name"] . "%");
        }
        $query = sprintf(
            "
                SELECT DISTINCT COALESCE(MB_CACHE_ARTIST.name, FIT.artist) AS name, MB_CACHE_ARTIST.image
                FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                %s
                ORDER BY name
                LIMIT :resultsPage
            ",
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null
        );
        $results = $dbh->query($query, $params);
        return ($results);
    }

    private function getMBIdFromName(string $name)
    {
        $query = " SELECT mbid FROM MB_CACHE_ARTIST WHERE name = :name ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":name", $name)
        );
        $results = $this->dbh->query($query, $params);
        if (count($results) == 1) {
            return ($results[0]->mbid);
        } else {
            return (null);
        }
    }

    public function get(): void
    {
        if (empty($this->mbId) && !empty($this->name)) {
            $this->mbId = $this->getMBIdFromName(($this->name));
        }
        if (!empty($this->mbId)) {
            $query = " SELECT name, image FROM MB_CACHE_ARTIST WHERE mbid = :mbid ";
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
            );
            $results = $this->dbh->query($query, $params);
            if (count($results) == 1) {
                $this->name = $results[0]->name;
                $this->image = $results[0]->image;
                $query = " SELECT url_relationship_typeid, url_relationship_value FROM MB_CACHE_ARTIST_URL_RELATIONSHIP WHERE artist_mbid = :mbid ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $results = $this->dbh->query($query, $params);
                if (count($results) > 0) {
                    foreach ($results as $result) {
                        $this->relations[] = (object) ["type-id" => $result->url_relationship_typeid, "url" => $result->url_relationship_value];
                    }
                } else {
                    $this->relations = [];
                }
                $query = " SELECT bio_summary, bio_content FROM MB_LASTFM_CACHE_ARTIST WHERE artist_mbid = :mbid ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $results = $this->dbh->query($query, $params);
                if (count($results) == 1) {
                    $this->bio = (object) [
                        "summary" => $results[0]->bio_summary,
                        "content" => $results[0]->bio_content
                    ];
                }
            } else {
                throw new \Spieldose\Exception\NotFoundException("mbId");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("mbId");
        }
    }
}

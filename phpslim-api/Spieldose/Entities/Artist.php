<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Artist extends \Spieldose\Entities\Entity
{
    public $name;
    public $image;
    public $relations = array();
    public object $bio;

    private function getMBIdFromName(string $name): ?string
    {
        $mbId = null;
        $query = " SELECT DISTINCT mb_artist_id AS mbId FROM FILE_ID3_TAG WHERE artist = :artist ORDER BY mb_artist_id NULLS LAST LIMIT 1 ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":artist", $name)
        );
        $results = $this->dbh->query($query, $params);
        if (count($results) == 1) {
            $mbId = $results[0]->mbId;
        }
        return ($mbId);
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

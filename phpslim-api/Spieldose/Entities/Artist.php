<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Artist extends \Spieldose\Entities\Entity
{
    public $name;
    public $image;
    public $relations = array();

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
            $query = " SELECT name, json FROM MB_CACHE_ARTIST WHERE mbid = :mbid ";
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
            );
            $results = $this->dbh->query($query, $params);
            if (count($results) == 1) {
                $this->name = $results[0]->name;
                $json = json_decode($results[0]->json ?? "{}");
                if (isset($json->relations) && is_array($json->relations)) {
                    foreach ($json->relations as $relation) {
                        // https://musicbrainz.org/relationships/artist-url
                        if ($relation->{"target-type"} == "url") {
                            switch ($relation->{"type-id"}) {
                                // image
                                case "221132e9-e30e-43f2-a741-15afc4c5fa7c":
                                    // official homepage
                                case "fe33d22f-c3b0-4d68-bd53-a856badf2b15":
                                    // last.fm
                                case "08db8098-c0df-4b78-82c3-c8697b4bba7f":
                                    // wikidata
                                case "689870a4-a1e4-4912-b17f-7b2664215698":
                                    // wikipedia
                                case "29651736-fa6d-48e4-aadc-a557c6add1cb":
                                    $item = new \stdClass();
                                    $item->id = $relation->{"type-id"};
                                    $item->name = $relation->type;
                                    $item->url = $relation->url;
                                    $this->relations[] = $item;
                                    break;
                            }
                        }
                    }
                }
            } else {
                throw new \Spieldose\Exception\NotFoundException("mbId");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("mbId");
        }
    }
}

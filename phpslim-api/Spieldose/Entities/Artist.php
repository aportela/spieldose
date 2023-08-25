<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Artist extends \Spieldose\Entities\Entity
{
    public $name = null;
    public $image = null;
    public $relations = null;
    public $bio = null;
    public $popularAlbum = null;
    public $latestAlbum = null;
    public $topTracks = null;


    public static function search(\aportela\DatabaseWrapper\DB $dbh, array $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $params = array();
        $filterConditions = array();
        if (isset($filter["name"]) && !empty($filter["name"])) {
            $filterConditions[] = " COALESCE(MB_CACHE_ARTIST.name, FIT.artist) LIKE :name";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":name", "%" . $filter["name"] . "%");
        } else {
            $filterConditions[] = " COALESCE(MB_CACHE_ARTIST.name, FIT.artist) IS NOT NULL ";
        }
        $fieldDefinitions = [
            "name" => "DISTINCT COALESCE(MB_CACHE_ARTIST.name, FIT.artist)",
            "image" => "MB_CACHE_ARTIST.image"
        ];
        $fieldCountDefinition = [
            "totalResults" => " COUNT(DISTINCT COALESCE(MB_CACHE_ARTIST.name, FIT.artist))"
        ];
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();

        $afterBrowseFunction = function ($data) {
            $data->items = array_map(
                function ($result) {
                    if (!empty($result->image)) {
                        $result->image = sprintf("api/2/thumbnail/normal/remote/artist/?url=%s", urlencode($result->image));
                    }
                    return ($result);
                },
                $data->items
            );
        };
        $browser = new \aportela\DatabaseBrowserWrapper\Browser($dbh, $fieldDefinitions, $fieldCountDefinition, $pager, $sort, $filter, $afterBrowseFunction);
        foreach ($params as $param) {
            $browser->addDBQueryParam($param);
        }

        $query = sprintf(
            "
                SELECT %s
                FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                %s
                %s
                %s
            ",
            $browser->getQueryFields(),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
            $browser->getQuerySort(),
            $pager->getQueryLimit()
        );
        $queryCount = sprintf(
            "
                SELECT
                %s
                FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                %s
            ",
            $browser->getQueryCountFields(),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null
        );
        $data = $browser->launch($query, $queryCount);
        return ($data);
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
                if (!empty($this->image)) {
                    $this->image = sprintf("api/2/thumbnail/normal/remote/artist/?url=%s", urlencode($this->image));
                }
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
                $query = " SELECT DISTINCT FIT.album, FIT.mb_album_id, FIT.year FROM FILE_ID3_TAG FIT WHERE FIT.mb_artist_id = :mbid AND FIT.album IS NOT NULL ORDER BY RANDOM() LIMIT 1";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $results = $this->dbh->query($query, $params);
                $this->popularAlbum = (object) [
                    'title' => null,
                    'year' => null,
                    'image' => null
                ];
                if (count($results) == 1) {
                    $this->popularAlbum->title = $results[0]->album;
                    $this->popularAlbum->year = $results[0]->year;
                    $coverArtURL = sprintf("https://coverartarchive.org/release/%s/front-250.jpg", $results[0]->mb_album_id);
                    $this->popularAlbum->image = sprintf("api/2/thumbnail/small/remote/album/?url=%s", urlencode($coverArtURL));
                }
                $query = " SELECT DISTINCT FIT.album, FIT.mb_album_id, FIT.year FROM FILE_ID3_TAG FIT WHERE FIT.mb_artist_id = :mbid AND FIT.album IS NOT NULL ORDER BY RANDOM() LIMIT 1";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $results = $this->dbh->query($query, $params);
                $this->latestAlbum = (object) [
                    'title' => null,
                    'year' => null,
                    'image' => null
                ];
                if (count($results) == 1) {
                    $this->latestAlbum->title = $results[0]->album;
                    $this->latestAlbum->year = $results[0]->year;
                    $coverArtURL = sprintf("https://coverartarchive.org/release/%s/front-250.jpg", $results[0]->mb_album_id);
                    $this->latestAlbum->image = sprintf("api/2/thumbnail/small/remote/album/?url=%s", urlencode($coverArtURL));
                }
                $query = sprintf(
                    "
                        SELECT FIT.id, FIT.title, FIT.artist, FIT.album, FIT.album_artist AS albumArtist, FIT.year, FIT.track_number as trackNumber, FIT.mb_album_id AS musicBrainzAlbumId
                        FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                        WHERE FIT.mb_artist_id = :mbid
                        AND FIT.title IS NOT NULL
                        ORDER BY RANDOM()
                        LIMIT 10
                    "
                );
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $this->topTracks = $this->dbh->query($query, $params);
                foreach ($this->topTracks as $track) {
                    $coverArtURL = sprintf("https://coverartarchive.org/release/%s/front-250.jpg", $track->musicBrainzAlbumId);
                    $track->image = sprintf("api/2/thumbnail/small/remote/album/?url=%s", urlencode($coverArtURL));
                }
            } else {
                throw new \Spieldose\Exception\NotFoundException("mbId");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("mbId");
        }
    }
}

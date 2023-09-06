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
    public $topAlbums = null;
    public $appearsOnAlbums = null;
    public $similar = null;


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
            "mbId" => "FIT.mb_artist_id",
            "name" => "COALESCE(MB_CACHE_ARTIST.name, FIT.artist)",
            "image" => "MB_CACHE_ARTIST.image"
        ];
        $fieldCountDefinition = [
            "totalResults" => " COUNT(DISTINCT FIT.mb_artist_id || COALESCE(MB_CACHE_ARTIST.name, FIT.artist))"
        ];
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();

        $afterBrowseFunction = function ($data) {
            $data->items = array_map(
                function ($result) {
                    if (!empty($result->image)) {
                        $result->image = sprintf(\Spieldose\API::REMOTE_COVER_URL_NORMAL_THUMBNAIL, urlencode($result->image));
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
                SELECT DISTINCT %s
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
        // TODO: get if no mbId
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
                $trackFields = [
                    "id " => "FIT.id",
                    "mbId" => "FIT.mb_release_track_id",
                    "title" => "FIT.title",
                    "artistMBId" => "FIT.mb_artist_id",
                    "artistName" => "COALESCE(MB_CACHE_ARTIST.name, FIT.artist)",
                    "releaseMBId" => "FIT.mb_album_id",
                    "releaseTitle" => "COALESCE(MB_CACHE_RELEASE.title, FIT.album)",
                    "albumArtistMBId" => "COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id)",
                    "albumArtistName" => "COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist)",
                    "year" => "COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))",
                    "trackNumber" => "FIT.track_number",
                    "coverPathId" => "D.id"
                ];

                $fields = [];
                foreach ($trackFields as $key => $value) {
                    $fields[] = $value . " AS " . $key;
                }
                $query = sprintf(
                    "
                        SELECT
                            %s
                        FROM FILE_ID3_TAG FIT
                        INNER JOIN FILE F ON F.id = FIT.id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                        LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                        WHERE FIT.mb_artist_id = :mbid
                        ORDER BY RANDOM()
                        LIMIT 10
                    ",
                    implode(", ", $fields)
                );
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                foreach ($this->dbh->query($query, $params) as $result) {
                    $this->topTracks[] = new \Spieldose\Entities\Track(
                        $result->id,
                        $result->mbId,
                        $result->title,
                        $result->artistMBId,
                        $result->artistName,
                        $result->releaseMBId,
                        $result->releaseTitle,
                        $result->albumArtistMBId,
                        $result->albumArtistName,
                        $result->year,
                        $result->trackNumber,
                        $result->coverPathId
                    );
                }
                $query = sprintf(
                    "
                        SELECT DISTINCT
                            FIT.mb_album_id AS mbId,
                            COALESCE(MB_CACHE_RELEASE.title, FIT.album) AS title,
                            COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist) AS artistName,
                            COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id) AS artistMBId,
                            COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT)) AS year,
                            D.id AS coverPathId
                        FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                        LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                        WHERE COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id) = :mbid
                        GROUP BY FIT.mb_album_id
                        ORDER BY COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))
                    "
                );
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $this->topAlbums = $this->dbh->query($query, $params);
                foreach ($this->topAlbums as $album) {
                    $album->artist = new \stdClass();
                    $album->artist->mbId = $album->artistMBId;
                    $album->artist->name = $album->artistName;
                    if (!empty($album->mbId)) {
                        $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                        $album->covertArtArchiveURL = $cover->getReleaseImageURL($album->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                    } else {
                        $album->covertArtArchiveURL = null;
                    }
                    unset($album->artistMbId);
                    unset($album->artistName);
                }
                $query = sprintf(
                    "
                        SELECT DISTINCT
                            FIT.mb_album_id AS mbId,
                            COALESCE(MB_CACHE_RELEASE.title, FIT.album) AS title,
                            COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist) AS artistName,
                            COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id) AS artistMBId,
                            COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT)) AS year,
                            D.id AS coverPathId
                        FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                        LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                        WHERE FIT.mb_artist_id = :mbid
                        AND COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id) <> :mbid
                        GROUP BY FIT.mb_album_id
                        ORDER BY COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))
                    "
                );
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $this->appearsOnAlbums = $this->dbh->query($query, $params);
                foreach ($this->appearsOnAlbums as $album) {
                    $album->artist = new \stdClass();
                    $album->artist->mbId = $album->artistMBId;
                    $album->artist->name = $album->artistName;
                    if (!empty($album->mbId)) {
                        $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                        $album->covertArtArchiveURL = $cover->getReleaseImageURL($album->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                    } else {
                        $album->covertArtArchiveURL = null;
                    }
                    unset($album->artistMbId);
                    unset($album->artistName);
                }
                $query = sprintf(
                    "
                        SELECT mbid, name, image FROM MB_CACHE_ARTIST
                        WHERE image IS NOT NULL
                        ORDER BY RANDOM()
                        LIMIT 32
                    "
                );
                $this->similar = $this->dbh->query($query, []);
                foreach ($this->similar as $similar) {
                    $similar->image = sprintf("api/2/thumbnail/small/remote/artist/?url=%s", urlencode($similar->image));
                }
            } else {
                throw new \Spieldose\Exception\NotFoundException("mbId");
            }
        } else {

            $this->relations = [];
            $this->bio = (object) [
                "summary" => null,
                "content" => null
            ];

            $query = " SELECT DISTINCT FIT.album, FIT.mb_album_id, FIT.year FROM FILE_ID3_TAG FIT WHERE FIT.artist = :name AND FIT.album IS NOT NULL ORDER BY RANDOM() LIMIT 1";
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name)
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
            $query = " SELECT DISTINCT FIT.album, FIT.mb_album_id, FIT.year FROM FILE_ID3_TAG FIT WHERE FIT.artist = :name AND FIT.album IS NOT NULL ORDER BY RANDOM() LIMIT 1";
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name)
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
                        SELECT
                            FIT.id,
                            FIT.title,
                            FIT.artist,
                            FIT.album,
                            FIT.album_artist AS albumArtist,
                            FIT.year,
                            FIT.track_number as trackNumber,
                            FIT.mb_album_id AS musicBrainzAlbumId,
                            D.id AS coverPathId
                        FROM FILE_ID3_TAG FIT
                        INNER JOIN FILE F ON F.ID = FIT.id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        WHERE FIT.artist = :name
                        AND FIT.title IS NOT NULL
                        ORDER BY RANDOM()
                        LIMIT 10
                    "
            );
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name)
            );
            $this->topTracks = $this->dbh->query($query, $params);
            foreach ($this->topTracks as $track) {
                if (!empty($track->musicBrainzAlbumId)) {
                    $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                    $track->covertArtArchiveURL = $cover->getReleaseImageURL($track->musicBrainzAlbumId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                } else {
                    $track->covertArtArchiveURL = null;
                }
            }
            $query = sprintf(
                "
                        SELECT DISTINCT
                            FIT.mb_album_id AS mbId,
                            COALESCE(MB_CACHE_RELEASE.title, FIT.album) AS title,
                            COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist, MB_CACHE_ARTIST.name, FIT.artist) AS artistName,
                            COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_artist_id, FIT.mb_artist_id) AS artistMBId,
                            COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT)) AS year,
                            D.id AS coverPathId
                        FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                        LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                        WHERE FIT.artist = :name
                        GROUP BY FIT.mb_album_id
                        ORDER BY COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))
                    "
            );
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name)
            );
            $this->topAlbums = $this->dbh->query($query, $params);
            foreach ($this->topAlbums as $album) {
                $album->artist = new \stdClass();
                $album->artist->mbId = $album->artistMBId;
                $album->artist->name = $album->artistName;
                if (!empty($album->mbId)) {
                    $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                    $album->covertArtArchiveURL = $cover->getReleaseImageURL($album->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                } else {
                    $album->covertArtArchiveURL = null;
                }
                unset($album->artistMbId);
                unset($album->artistName);
            }
            $query = sprintf(
                "
                        SELECT mbid, name, image FROM MB_CACHE_ARTIST
                        WHERE image IS NOT NULL
                        ORDER BY RANDOM()
                        LIMIT 32
                    "
            );
            $this->similar = $this->dbh->query($query, []);
            foreach ($this->similar as $similar) {
                $similar->image = sprintf("api/2/thumbnail/small/remote/artist/?url=%s", urlencode($similar->image));
            }
        }
    }
}

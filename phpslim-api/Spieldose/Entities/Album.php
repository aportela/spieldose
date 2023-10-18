<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Album extends \Spieldose\Entities\Entity
{
    public $mbId;
    public string $title;
    public object $artist;
    public ?int $year;
    public ?string $pathId;

    public function __construct(string $mbId = null, string $title, ?int $year, ?object $artist)
    {
        $this->mbId = $mbId;
        $this->title = $title;
        $this->year = $year;
        $this->artist = $artist;
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh, bool $useLocalCovers = true)
    {
        if (!empty($this->mbId)) {
            $query = "
                SELECT title, year, artist_mbid, artist_name, media_count
                FROM CACHE_RELEASE_MUSICBRAINZ
                WHERE mbid = :mbid
            ";
            $params = [new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)];
            $results = $dbh->query($query, $params);
            if (count($results) == 1) {
                $this->title = $results[0]->title;
                $this->year = $results[0]->year;
                $this->artist = (object) ["mbId" => $results[0]->artist_mbid, "name" => $results[0]->artist_name];
            } else {
                throw new \Spieldose\Exception\NotFoundException("mbid");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("mbid");
        }
    }

    public static function getAlbumLocalPathCoverFromPathId(\aportela\DatabaseWrapper\DB $dbh, string $pathId): ?string
    {
        $params = [new \aportela\DatabaseWrapper\Param\StringParam(":pathId", $pathId)];
        $results = $dbh->query("SELECT D.path AS localCoverPath, D.cover_filename AS localCoverFilename FROM DIRECTORY D WHERE D.id = :pathId", $params);
        if (count($results) == 1) {
            if ($results[0]->localCoverPath && $results[0]->localCoverFilename) {
                $localPath = $results[0]->localCoverPath . DIRECTORY_SEPARATOR . $results[0]->localCoverFilename;
                return ($localPath);
            }
        }
        return (null);
    }

    public static function search(\aportela\DatabaseWrapper\DB $dbh, array $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager, bool $useLocalCovers): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $params = array();
        $filterConditions = array(
            " COALESCE(MB_CACHE_RELEASE.title, FIT.album) IS NOT NULL "
        );
        if (isset($filter["title"]) && !empty($filter["title"])) {
            $words = explode(" ", trim($filter["title"]));
            foreach ($words as $word) {
                $paramName = ":title_" . uniqid();
                $filterConditions[] = sprintf(" COALESCE(MB_CACHE_RELEASE.title, FIT.album) LIKE %s", $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        }
        if (isset($filter["albumArtistName"]) && !empty($filter["albumArtistName"])) {
            $words = explode(" ", trim($filter["albumArtistName"]));
            foreach ($words as $word) {
                $paramName = ":albumArtistName_" . uniqid();
                $filterConditions[] = sprintf(" COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist) LIKE %s", $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        }
        if (isset($filter["text"]) && !empty($filter["text"])) {
            $words = explode(" ", trim($filter["text"]));
            foreach ($words as $word) {
                $paramName = ":text_" . uniqid();
                $filterConditions[] = sprintf(" ( COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist) LIKE %s OR COALESCE(MB_CACHE_RELEASE.title, FIT.album) LIKE %s )", $paramName, $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        }
        $fieldDefinitions = [
            "mbId" => "FIT.mb_album_id",
            "title" => "COALESCE(MB_CACHE_RELEASE.title, FIT.album)",
            "albumArtistName" => "COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist)",
            "albumArtistMbId" => "COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id)",
            "year" => "COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))",
            "coverPathId" => "D.id"
        ];

        $fieldCountDefinition = [
            "totalResults" => " COUNT(*)"
        ];

        $afterBrowseFunction = function ($data) use ($useLocalCovers) {
            $data->items = array_map(
                function ($result) use ($useLocalCovers) {
                    $result->artist = new \stdClass();
                    $result->artist->mbId = $result->albumArtistMbId;
                    $result->artist->name = $result->albumArtistName;
                    unset($result->albumArtistMbId);
                    unset($result->albumArtistName);
                    if ($useLocalCovers && !empty($result->coverPathId)) {
                        $result->covers = [
                            "small" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_SMALL_THUMBNAIL, $result->coverPathId),
                            "normal" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_NORMAL_THUMBNAIL, $result->coverPathId)
                        ];
                    } elseif (!empty($result->mbId)) {
                        $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                        $url = $cover->getReleaseImageURL($result->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                        $result->covers = [
                            "small" => sprintf(\Spieldose\API::REMOTE_COVER_URL_SMALL_THUMBNAIL, $url),
                            "normal" => sprintf(\Spieldose\API::REMOTE_COVER_URL_NORMAL_THUMBNAIL, $url)
                        ];
                    } else {
                        $result->covers = [
                            "small" => null,
                            "normal" => null
                        ];
                    }
                    unset($result->coverPathId);
                    // create a unique hash for this element, this is done because artist name && artist musicbrainz are not both mandatory and can not be used as key on vue v-for
                    $result->hash = md5($result->mbId . $result->title);
                    return ($result);
                },
                $data->items
            );
        };

        $browser = new \aportela\DatabaseBrowserWrapper\Browser($dbh, $fieldDefinitions, $fieldCountDefinition, $pager, $sort, new \aportela\DatabaseBrowserWrapper\Filter(), $afterBrowseFunction);

        foreach ($params as $param) {
            $browser->addDBQueryParam($param);
        }

        $query = sprintf(
            "
                SELECT %s
                FROM FILE_ID3_TAG FIT
                INNER JOIN FILE F ON F.ID = FIT.id
                LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                %s
                GROUP BY FIT.mb_album_id, COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist), COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id), COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))
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
                SELECT %s
                FROM (
                    SELECT %s
                    FROM FILE_ID3_TAG FIT
                    INNER JOIN FILE F ON F.ID = FIT.id
                    LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                    LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                    %s
                    GROUP BY FIT.mb_album_id, COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist), COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id), COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))
                )

            ",
            $browser->getQueryCountFields(),
            $browser->getQueryFields(),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
        );
        $data = $browser->launch($query, $queryCount);
        return ($data);
    }

    public static function getTrackIds(\aportela\DatabaseWrapper\DB $dbh, array $filter): array
    {
        $params = [];
        $whereConditions = [];
        if (isset($filter["mbId"]) && !empty($filter["mbId"])) {
            $whereConditions[] = " FIT.mb_album_id = :mbid ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $filter["mbId"]);
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("filter");
        }
        $query = sprintf(
            "
                SELECT F.id
                FROM FILE_ID3_TAG FIT
                INNER JOIN FILE F ON F.ID = FIT.id
                LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                %s
                ORDER BY FIT.track_number
            ",
            count($whereConditions) ? " WHERE " . implode(" AND ", $whereConditions) : null
        );
        $data = $dbh->query($query, $params);
        $ids = [];
        foreach ($data as $item) {
            $ids[] = $item->id;
        }
        return ($ids);
    }
}

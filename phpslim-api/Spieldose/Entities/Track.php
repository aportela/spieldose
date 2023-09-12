<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Track extends \Spieldose\Entities\Entity
{

    public string $id;
    public ?string $url;
    public ?string $title;
    public object $artist;
    public object $album;
    public ?int $trackNumber;
    public array $covers;
    public ?int $favorited;

    public function __construct(string $id, ?string $mbId = null, ?string $title = null, ?string $artistMBId = null, ?string $artistName = null, ?string $albumMBId = null, ?string $albumTitle = null, ?string $albumArtistMBId = null, ?string $albumArtistName = null, ?int $year = null, ?int $trackNumber = null, ?string $coverPathId = null, ?int $favorited = null)
    {
        $this->id = $id;
        $this->url = sprintf(\Spieldose\API::FILE_URL, $id);
        $this->mbId = $mbId;
        $this->title = $title;
        $this->artist = new \stdClass();
        $this->artist->mbId = $artistMBId;
        $this->artist->name = $artistName;
        $this->album = new \stdClass();
        $this->album->mbId = $albumMBId;
        $this->album->title = $albumTitle;
        $this->album->year = $year;
        $this->album->artist = new \stdClass();
        $this->album->artist->mbId = $albumArtistMBId;
        $this->album->artist->name = $albumArtistName;
        $this->trackNumber = $trackNumber;
        if (!empty($coverPathId)) {
            $this->covers = [
                "small" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_SMALL_THUMBNAIL, $coverPathId),
                "normal" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_NORMAL_THUMBNAIL, $coverPathId)
            ];
        } else if (!empty($this->album->mbId)) {
            $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
            $this->covers = [
                "small" => sprintf(\Spieldose\API::REMOTE_COVER_URL_SMALL_THUMBNAIL, urlencode($cover->getReleaseImageURL($this->album->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL))),
                "normal" => sprintf(\Spieldose\API::REMOTE_COVER_URL_NORMAL_THUMBNAIL, urlencode($cover->getReleaseImageURL($this->album->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL))),
            ];
        } else {
            $this->covers = [
                "small" => null,
                "normal" => null
            ];
        }
        $this->favorited = $favorited;
    }

    public function __destruct()
    {
    }

    public static function search(\aportela\DatabaseWrapper\DB $dbh, $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId())
        );
        $filterConditions = array();
        $leftJoins = array();
        if (isset($filter["title"]) && !empty($filter["title"])) {
            $words = explode(" ", trim($filter["title"]));
            foreach ($words as $word) {
                $paramName = ":title_" . uniqid();
                $filterConditions[] = sprintf(" FIT.title LIKE %s", $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        }
        if (isset($filter["artistName"]) && !empty($filter["artistName"])) {
            $words = explode(" ", trim($filter["artistName"]));
            foreach ($words as $word) {
                $paramName = ":artistname_" . uniqid();
                $filterConditions[] = sprintf(" COALESCE(MB_CACHE_ARTIST.name, FIT.artist) LIKE %s", $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        }
        if (isset($filter["text"]) && !empty($filter["text"])) {
            $words = explode(" ", trim($filter["text"]));
            foreach ($words as $word) {
                $paramName = ":text_" . uniqid();
                $filterConditions[] = sprintf(" (FIT.title LIKE %s OR COALESCE(MB_CACHE_ARTIST.name, FIT.artist) LIKE %s OR COALESCE(MB_CACHE_RELEASE.title, FIT.album) LIKE %s) ", $paramName, $paramName, $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        }
        if (isset($filter["path"]) && !empty($filter["path"])) {
            $filterConditions[] = " EXISTS (SELECT DIRECTORY.id FROM FILE INNER JOIN DIRECTORY ON DIRECTORY.id = FILE.directory_id WHERE FILE.id = F.id AND DIRECTORY.id = :path) ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":path", $filter["path"]);
        }
        if (isset($filter["playlistId"]) && !empty($filter["playlistId"])) {
            if ($filter["playlistId"] == "00000000-0000-0000-0000-000000000000") {
                $filterConditions[] = " EXISTS ( SELECT * FROM FILE_FAVORITE WHERE FILE_FAVORITE.user_id = :user_id AND FILE_FAVORITE.file_id = F.id ) ";
            } else {
                $filterConditions[] = " EXISTS (SELECT PT.playlist_id FROM PLAYLIST_TRACK PT WHERE PT.playlist_id = :playlist_id AND PT.track_id = F.id) ";
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $filter["playlistId"]);
                $leftJoins[] = " LEFT JOIN PLAYLIST_TRACK ON PLAYLIST_TRACK.playlist_id = :playlist_id AND PLAYLIST_TRACK.track_id = F.id ";
            }
        }
        if (isset($filter["albumMbId"]) && !empty($filter["albumMbId"])) {
            $filterConditions[] = " FIT.mb_album_id = :mb_album_id ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_album_id", $filter["albumMbId"]);
        }
        if (isset($filter["albumTitle"]) && !empty($filter["albumTitle"])) {
            $filterConditions[] = " COALESCE(MB_CACHE_RELEASE.title, FIT.album) = :album_title ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":album_title", $filter["albumTitle"]);
        }
        if (isset($filter["year"]) && !empty($filter["year"])) {
            $filterConditions[] = " COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT)) = :year ";
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":year", $filter["year"]);
        }
        $fieldDefinitions = [
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
            "coverPathId" => "D.id",
            "favorited" => "FF.favorited"
        ];

        if (isset($filter["playlistId"]) && !empty($filter["playlistId"])) {
            if ($filter["playlistId"] == "00000000-0000-0000-0000-000000000000") {
                $fieldDefinitions["playListTrackIndex"] = "FF.favorited";
            } else {
                $fieldDefinitions["playListTrackIndex"] = "PLAYLIST_TRACK.track_index";
            }
        }
        $fieldCountDefinition = [
            "totalResults" => " COUNT(FIT.id)"
        ];
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();

        $afterBrowseFunction = function ($data) {
            $data->items = array_map(
                function ($result) {
                    $result = new \Spieldose\Entities\Track(
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
                        $result->coverPathId,
                        $result->favorited
                    );
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
                SELECT
                %s
                FROM FILE_ID3_TAG FIT
                INNER JOIN FILE F ON F.id = FIT.id
                LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                LEFT JOIN FILE_FAVORITE FF ON FF.file_id = FIT.id AND FF.user_id = :user_id
                %s
                %s
                %s
                %s
            ",
            $browser->getQueryFields(),
            count($leftJoins) > 0 ? implode(PHP_EOL, $leftJoins) : null,
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
            $browser->getQuerySort(),
            $pager->getQueryLimit()
        );
        $queryCount = sprintf(
            "
                SELECT
                    %s
                FROM FILE_ID3_TAG FIT
                INNER JOIN FILE F ON F.id = FIT.id
                LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                LEFT JOIN FILE_FAVORITE FF ON FF.file_id = FIT.id AND FF.user_id = :user_id
                %s
            ",
            $browser->getQueryCountFields(),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null
        );
        $data = $browser->launch($query, $queryCount);
        return ($data);
    }

    public function increasePlayCount(\aportela\DatabaseWrapper\DB $dbh)
    {
        if (!empty($this->id)) {
            $query = " INSERT OR IGNORE INTO FILE_PLAYCOUNT_STATS (file_id, user_id, play_timestamp) VALUES (:file_id, :user_id, strftime('%s', 'now')) ";
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $this->id),
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId())
            );
            $dbh->exec($query, $params);
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function toggleFavorite(\aportela\DatabaseWrapper\DB $dbh, bool $flag)
    {
        if (!empty($this->id)) {
            $query = null;
            if ($flag) {
                $query = " INSERT INTO FILE_FAVORITE (file_id, user_id, favorited) VALUES (:file_id, :user_id, strftime('%s', 'now')) ON CONFLICT (file_id, user_id) DO UPDATE SET favorited = strftime('%s', 'now') ";
            } else {
                $query = " DELETE FROM FILE_FAVORITE WHERE file_id = :file_id AND user_id = :user_id ";
            }
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $this->id),
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId())
            );
            $dbh->exec($query, $params);
            if ($flag) {
                $query = " SELECT favorited FROM FILE_FAVORITE WHERE file_id = :file_id AND user_id = :user_id ";
                $data = $dbh->query($query, $params);
                $this->favorited = count($data) == 1 ? $data[0]->favorited : null;
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }
}

<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Track extends \Spieldose\Entities\Entity
{

    public string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function __destruct()
    {
    }

    public static function search(\aportela\DatabaseWrapper\DB $dbh, $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): array
    {
        $params = array();
        $filterConditions = array();
        if (isset($filter["title"]) && !empty($filter["title"])) {
            $filterConditions[] = " FIT.title LIKE :title";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":title", "%" . $filter["title"] . "%");
        }
        if (isset($filter["artist"]) && !empty($filter["artist"])) {
            $filterConditions[] = " FIT.artist LIKE :artist";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":artist", "%" . $filter["artist"] . "%");
        }
        if (isset($filter["text"]) && !empty($filter["text"])) {
            $filterConditions[] = " (FIT.title LIKE :text OR FIT.artist LIKE :text OR FIT.album LIKE :text) ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":text", "%" . $filter["text"] . "%");
        }
        if (isset($filter["path"]) && !empty($filter["path"])) {
            $filterConditions[] = " EXISTS (SELECT DIRECTORY.id FROM FILE INNER JOIN DIRECTORY ON DIRECTORY.id = FILE.directory_id WHERE FILE.id = F.id AND DIRECTORY.id = :path)";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":path", $filter["path"]);
        }
        if (isset($filter["albumMbId"]) && !empty($filter["albumMbId"])) {
            $filterConditions[] = " FIT.mb_album_id = :mb_album_id ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_album_id", $filter["albumMbId"]);
        }
        $fieldDefinitions = [
            "id " => "FIT.id",
            "title" => "FIT.title",
            "artist" => "FIT.artist",
            "album" => "FIT.album",
            "albumArtist" => "FIT.album_artist",
            "year" => "FIT.year",
            "trackNumber" => "FIT.track_number",
            "musicBrainzAlbumId" => "FIT.mb_album_id",
            "coverPathId" => "D.id"
        ];
        $fieldCountDefinition = [
            "totalResults" => " COUNT(FIT.id)"
        ];
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();

        $afterBrowseFunction = function ($data) {
            $data->items = array_map(
                function ($result) {
                    if (!empty($result->musicBrainzAlbumId)) {
                        $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\apiFormat::JSON);
                        $result->covertArtArchiveURL = $cover->getReleaseImageURL($result->musicBrainzAlbumId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                    } else {
                        $result->covertArtArchiveURL = null;
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
                SELECT
                %s
                FROM FILE_ID3_TAG FIT
                INNER JOIN FILE F ON F.id = FIT.id
                LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
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
                FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.id = FIT.id
                %s
            ",
            $browser->getQueryCountFields(),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null
        );
        $data = $browser->launch($query, $queryCount);
        return ($data->items);
    }

    public function increasePlayCount(\aportela\DatabaseWrapper\DB $dbh)
    {
        $query = " INSERT OR IGNORE INTO FILE_PLAYCOUNT_STATS (file_id, user_id, play_timestamp) VALUES (:file_id, :user_id, strftime('%s', 'now')) ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $this->id),
            new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId())
        );
        $dbh->exec($query, $params);
    }
}

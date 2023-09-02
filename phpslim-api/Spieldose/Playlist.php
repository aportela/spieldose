<?php

declare(strict_types=1);

namespace Spieldose;

class Playlist
{
    public $id;
    public $name;
    public $tracks;

    public function __construct(string $id, string $name, array $tracks = array())
    {
        $this->id = $id;
        $this->name = $name;
        $this->tracks = $tracks;
    }

    public function __destruct()
    {
    }

    public function isAllowed(\Spieldose\Database\DB $dbh)
    {
        if (!empty($this->id)) {
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id)
            );
            $query = sprintf(
                '
                    SELECT P.user_id AS userId
                    FROM PLAYLIST P
                    WHERE P.id = :id
                '
            );
            $data = $dbh->query($query, $params);
            if (count($data) == 1) {
                return ($data[0]->userId == \Spieldose\UserSession::getUserId());
            } else {
                throw new \Spieldose\Exception\NotFoundException("id: " . $this->id);
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function add(\Spieldose\Database\DB $dbh)
    {
        if (!empty($this->id)) {
            if (!empty($this->name)) {
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                    new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId()),
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name)
                );
                $dbh->exec(" INSERT INTO PLAYLIST (id, user_id, name, ctime, mtime) VALUES(:id, :user_id, :name, strftime('%s', 'now'), strftime('%s', 'now')) ", $params);
                if (is_array($this->tracks) && count($this->tracks) > 0) {
                    foreach ($this->tracks as $trackIndex => $trackId) {
                        $params = array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                            new \aportela\DatabaseWrapper\Param\StringParam(":track_id", $trackId),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":track_index", $trackIndex)
                        );
                        $dbh->exec(" INSERT INTO PLAYLIST_TRACK (playlist_id, track_id, track_index) VALUES(:playlist_id, :track_id, :track_index) ", $params);
                    }
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("name");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function update(\Spieldose\Database\DB $dbh)
    {
        if (!empty($this->id)) {
            if (!empty($this->name)) {
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                    new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId()),
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name)
                );
                $dbh->exec(" UPDATE SET name = :name, mtime = strftime('%s', 'now') WHERE id = :id AND user_id = :user_id ", $params);
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id)
                );
                $dbh->exec(" DELETE FROM PLAYLIST_TRACK WHERE playlist_id = :playlist_id  ", $params);
                if (is_array($this->tracks) && count($this->tracks) > 0) {
                    foreach ($this->tracks as $trackIndex => $trackId) {
                        $params = array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                            new \aportela\DatabaseWrapper\Param\StringParam(":track_id", $trackId),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":track_index", $trackIndex)
                        );
                        $dbh->exec(" INSERT INTO PLAYLIST_TRACK (playlist_id, track_id, track_index) VALUES(:playlist_id, :track_id, :track_index) ", $params);
                    }
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("name");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function remove(\Spieldose\Database\DB $dbh)
    {
        if (!empty($this->id)) {
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id)
            );
            $dbh->exec(" DELETE FROM PLAYLIST_TRACK WHERE playlist_id = :playlist_id  ", $params);
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId())
            );
            $dbh->exec(" DELETE FROM PLAYLIST WHERE id = :id AND user_id = :user_id ", $params);
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function get(\Spieldose\Database\DB $dbh)
    {
        if (!empty($this->id)) {
            $params = array();
            $params[] = (new \Spieldose\Database\DBParam())->str(":id", $this->id);
            $query = sprintf(
                '
                    SELECT P.id, P.name
                    FROM PLAYLIST P
                    WHERE P.id = :id
                    '
            );
            $data = $dbh->query($query, $params);
            if (count($data) == 1) {
                $this->id = $data[0]->id;
                $this->name = $data[0]->name;
                $this->tracks = $this->getPlaylistTracks($dbh);
            } else {
                throw new \Spieldose\Exception\NotFoundException("");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    private function getPlaylistTracks(\Spieldose\Database\DB $dbh)
    {
        $params = array(
            (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId()),
            (new \Spieldose\Database\DBParam())->str(":playlist_id", $this->id)
        );
        $whereCondition = " AND PT.playlist_id = :playlist_id ";
        $sqlOrder = "";
        $query = sprintf(
            '
                SELECT DISTINCT
                    id,
                    F.track_number AS number,
                    COALESCE(MBT.track, F.track_name) AS title,
                    COALESCE(MBA2.artist, F.track_artist) AS artist,
                    COALESCE(MBA1.album, F.album_name) AS album,
                    album_artist AS albumartist,
                    COALESCE(MBA1.year, F.year) AS year,
                    playtime_seconds AS playtimeSeconds,
                    playtime_string AS playtimeString,
                    MBA1.image AS image,
                    genre,
                    mime,
                    COALESCE(LF.loved, 0) AS loved
                FROM FILE F
                INNER JOIN PLAYLIST_TRACK PT ON PT.file_id = F.id
                LEFT JOIN MB_CACHE_TRACK MBT ON MBT.mbid = F.track_mbid
                LEFT JOIN MB_CACHE_ALBUM MBA1 ON MBA1.mbid = F.album_mbid
                LEFT JOIN MB_CACHE_ARTIST MBA2 ON MBA2.mbid = F.artist_mbid
                LEFT JOIN LOVED_FILE LF ON (LF.file_id = F.id AND LF.user_id = :user_id)
                WHERE COALESCE(MBT.track, F.track_name) IS NOT NULL
                %s
                %s
                ',
            $whereCondition,
            $sqlOrder
        );
        return ($dbh->query($query, $params));
    }

    private static function getPlaylistCovers(\aportela\DatabaseWrapper\DB $dbh, string $playlistId)
    {
        $covers = [];
        foreach ($dbh->query(" SELECT id FROM DIRECTORY WHERE cover_filename IS NOT NULL ORDER BY RANDOM() LIMIT 6") as $cover) {
            $covers[] = sprintf("api/2/thumbnail/small/local/album/?path=%s", $cover->id);
        }
        return ($covers);
    }

    public static function search(\aportela\DatabaseWrapper\DB $dbh, array $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId())
        );
        $filterConditions = array(
            " PLAYLIST.user_id = :user_id"
        );
        if (isset($filter["name"]) && !empty($filter["name"])) {
            $filterConditions[] = " PLAYLIST.name LIKE :name";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":name", "%" . $filter["name"] . "%");
        }
        $fieldDefinitions = [
            "id" => "PLAYLIST.id",
            "name" => "PLAYLIST.name",
            "trackCount" => "COUNT(*)"
        ];
        $fieldCountDefinition = [
            "totalResults" => " COUNT(PLAYLIST.id)"
        ];
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();

        $afterBrowseFunction = function ($data) use ($dbh) {
            $data->items = array_map(
                function ($result) use ($dbh) {
                    $result->covers = self::getPlaylistCovers($dbh, $result->id);
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
                FROM PLAYLIST
                LEFT JOIN PLAYLIST_TRACK ON PLAYLIST.id = PLAYLIST_TRACK.playlist_id
                %s
                GROUP BY PLAYLIST.id
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
                FROM PLAYLIST
                %s
            ",
            $browser->getQueryCountFields(),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null
        );
        $data = $browser->launch($query, $queryCount);

        return ($data);
    }
}

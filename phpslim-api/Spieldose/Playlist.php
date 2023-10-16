<?php

declare(strict_types=1);

namespace Spieldose;

class Playlist
{
    public const FAVORITE_TRACKS_PLAYLIST_ID = "00000000-0000-0000-0000-000000000000";
    public string $id;
    public string $name;
    public $ctime = null;
    public $mtime = null;
    public $owner = null;
    public $public = false;
    public array $tracks = [];

    public function __construct(string $id, string $name, array $tracks = [], bool $public = false, ?string $ownerId = null, string $ownerName = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->tracks = $tracks;
        $this->public = $public;
        $this->owner = new \stdClass();
        $this->owner->id = $ownerId;
        $this->owner->name = $ownerName;
    }

    public function __destruct()
    {
    }

    public function allowView(\aportela\DatabaseWrapper\DB $dbh): bool
    {
        if (!empty($this->id)) {
            // special playlists, user allowed
            if ($this->id == self::FAVORITE_TRACKS_PLAYLIST_ID) {
                return (true);
            } else {
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id)
                );
                $query = sprintf(
                    '
                        SELECT P.user_id AS userId, P.public
                        FROM PLAYLIST P
                        WHERE P.id = :id
                    '
                );
                $data = $dbh->query($query, $params);
                if (count($data) == 1) {
                    return ($data[0]->userId == \Spieldose\UserSession::getUserId() || $data[0]->public == "S");
                } else {
                    throw new \Spieldose\Exception\NotFoundException("id: " . $this->id);
                }
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function allowUpdate(\aportela\DatabaseWrapper\DB $dbh): bool
    {
        if (!empty($this->id)) {
            // special playlists, user allowed
            if ($this->id == self::FAVORITE_TRACKS_PLAYLIST_ID) {
                return (false);
            } else {
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
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh): void
    {
        if (!empty($this->id)) {
            if ($this->allowView($dbh)) {
                $params = array();
                $query = null;
                if ($this->id != self::FAVORITE_TRACKS_PLAYLIST_ID) {
                    $query = "
                        SELECT P.id, P.name, P.ctime, P.mtime, P.name, P.public, P.user_id AS ownerId, U.name AS ownerName
                        FROM PLAYLIST P
                        LEFT JOIN USER U ON U.id = P.user_id
                        WHERE P.id = :id
                    ";
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id);
                } else {
                    $query = "
                        SELECT :uuid_zero AS id, 'My favorite tracks' AS name, MIN(FF.favorited) AS ctime, MAX(FF.favorited) AS mtime, '#' AS name, NULL AS public, :user_id AS ownerId, U.name AS ownerName
                        FROM USER U
                        LEFT JOIN FILE_FAVORITE FF ON FF.user_id = U.id
                        WHERE U.id = :user_id
                        GROUP BY (U.id)
                    ";
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId());
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":uuid_zero", self::FAVORITE_TRACKS_PLAYLIST_ID);
                }
                $data = $dbh->query($query, $params);
                if (count($data) == 1) {
                    $this->id = $data[0]->id;
                    $this->name = $data[0]->name;
                    $this->ctime = $data[0]->ctime;
                    $this->mtime = $data[0]->mtime;
                    $this->name = $data[0]->name;
                    $this->owner = new \stdClass();
                    $this->owner->id = $data[0]->ownerId;
                    $this->owner->name = $data[0]->ownerName;
                    $this->public = $data[0]->public ?? false;
                    if (!($this->public || $this->owner->id == \Spieldose\UserSession::getUserId() || $this->id == self::FAVORITE_TRACKS_PLAYLIST_ID)) {
                        throw new \Spieldose\Exception\AccessDeniedException("id");
                    } else {
                        $filter = array(
                            "playlistId" => $this->id
                        );
                        $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                            [
                                new \aportela\DatabaseBrowserWrapper\SortItem("playListTrackIndex", \aportela\DatabaseBrowserWrapper\Order::ASC, true)
                            ]
                        );
                        $pager = new \aportela\DatabaseBrowserWrapper\Pager(false, 1, 0);
                        $data = \Spieldose\Entities\Track::search($dbh, $filter, $sort, $pager);
                        $this->tracks = $data->items;
                    }
                } else {
                    throw new \Spieldose\Exception\NotFoundException("id: " . $this->id);
                }
            } else {
                throw new \Spieldose\Exception\AccessDeniedException("userId: " . \Spieldose\UserSession::getUserId());
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function add(\aportela\DatabaseWrapper\DB $dbh)
    {
        if (!empty($this->id)) {
            if (!empty($this->name)) {
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                    new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId()),
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name),
                    new \aportela\DatabaseWrapper\Param\StringParam(":public", $this->public ? "S" : "N")
                );
                $dbh->exec(" INSERT INTO PLAYLIST (id, user_id, name, ctime, mtime, public) VALUES(:id, :user_id, :name, strftime('%s', 'now'), strftime('%s', 'now'), :public) ", $params);
                if (is_array($this->tracks) && count($this->tracks) > 0) {
                    foreach ($this->tracks as $trackIndex => $trackId) {
                        $params = array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                            new \aportela\DatabaseWrapper\Param\StringParam(":track_id", $trackId),
                            new \aportela\DatabaseWrapper\Param\StringParam(":track_id", $trackId),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":track_index", $trackIndex)
                        );
                        $dbh->exec(" INSERT INTO PLAYLIST_TRACK (playlist_id, track_id, track_index) VALUES(:playlist_id, :track_id, :track_index) ", $params);
                    }
                }
                $cp = new \Spieldose\CurrentPlaylist();
                $cp->setLinkedPlaylist($dbh, $this->id);
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("name");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function update(\aportela\DatabaseWrapper\DB $dbh)
    {
        if (!empty($this->id)) {
            if ($this->allowUpdate($dbh)) {
                if (!empty($this->name)) {
                    $params = array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                        new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name),
                        new \aportela\DatabaseWrapper\Param\StringParam(":public", $this->public ? "S" : "N")
                    );
                    $dbh->exec(" UPDATE PLAYLIST SET name = :name, public = :public, mtime = strftime('%s', 'now') WHERE id = :id ", $params);
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
                    $cp = new \Spieldose\CurrentPlaylist();
                    $cp->setLinkedPlaylist($dbh, $this->id);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("name");
                }
            } else {
                throw new \Spieldose\Exception\AccessDeniedException("userId: " . \Spieldose\UserSession::getUserId());
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function remove(\aportela\DatabaseWrapper\DB $dbh)
    {
        if (!empty($this->id)) {
            if ($this->allowUpdate($dbh)) {
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id)
                );
                $dbh->exec(" UPDATE CURRENT_PLAYLIST SET playlist_id = NULL WHERE playlist_id = :playlist_id ", $params);
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
                throw new \Spieldose\Exception\AccessDeniedException("userId: " . \Spieldose\UserSession::getUserId());
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    private static function getPlaylistCovers(\aportela\DatabaseWrapper\DB $dbh, string $playlistId)
    {
        $covers = [];
        if ($playlistId == self::FAVORITE_TRACKS_PLAYLIST_ID) {
            foreach ($dbh->query(
                "
                   SELECT
                        DISTINCT DIRECTORY.id
                    FROM DIRECTORY
                    INNER JOIN FILE ON FILE.directory_id = DIRECTORY.id
                    WHERE DIRECTORY.cover_filename IS NOT NULL
                    AND EXISTS ( SELECT * FROM FILE_FAVORITE WHERE FILE_FAVORITE.user_id = :user_id AND FILE_FAVORITE.file_id = FILE.id )
                    ORDER BY RANDOM()
                    LIMIT 16
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId())
                ]
            ) as $cover) {
                $covers[] = sprintf("api/2/thumbnail/small/local/album/?path=%s", $cover->id);
            }
        } else {
            foreach ($dbh->query(
                "
                   SELECT
                        DISTINCT DIRECTORY.id
                    FROM DIRECTORY
                    INNER JOIN FILE ON FILE.directory_id = DIRECTORY.id
                    WHERE DIRECTORY.cover_filename IS NOT NULL
                    AND EXISTS ( SELECT * FROM PLAYLIST_TRACK WHERE PLAYLIST_TRACK.playlist_id = :playlist_id AND FILE.id = PLAYLIST_TRACK.track_id )
                    ORDER BY RANDOM()
                    LIMIT 16
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $playlistId)
                ]
            ) as $cover) {
                $covers[] = sprintf("api/2/thumbnail/small/local/album/?path=%s", $cover->id);
            }
        }

        return ($covers);
    }

    public static function search(\aportela\DatabaseWrapper\DB $dbh, array $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":uuid_zero", self::FAVORITE_TRACKS_PLAYLIST_ID),
            new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId())
        );
        $filterConditions = array(
            " PLAYLIST.user_id = :user_id OR PLAYLIST.public = 'S' "
        );
        if (isset($filter["name"]) && !empty($filter["name"])) {
            $filterConditions[] = " PLAYLIST.name LIKE :name";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":name", "%" . $filter["name"] . "%");
        }
        $fieldDefinitions = [
            "id" => "PLAYLIST.id",
            "name" => "PLAYLIST.name",
            "trackCount" => "COUNT(*)",
            "ownerId" => "PLAYLIST.user_id",
            "ownerName" => "USER.name",
            "updated" =>  "PLAYLIST.mtime"
        ];
        $fieldCountDefinition = [
            "totalResults" => " SUM(total)"
        ];
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();

        $afterBrowseFunction = function ($data) use ($dbh) {
            $data->items = array_map(
                function ($result) use ($dbh) {
                    $result->covers = self::getPlaylistCovers($dbh, $result->id);
                    $result->owner = new \stdClass();
                    $result->owner->id = $result->ownerId;
                    $result->owner->name = $result->ownerName;
                    unset($result->ownerId);
                    unset($result->ownerName);
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
                SELECT *
                FROM (
                    SELECT :uuid_zero AS id, 'My favorite tracks' AS name, COUNT(file_id) AS trackCount, :user_id AS ownerId, USER.email AS ownerName, MAX(FF.favorited) AS updated
                    FROM FILE_FAVORITE FF
                    LEFT JOIN USER ON USER.id = :user_id
                    WHERE FF.user_id = :user_id
                    GROUP BY FF.user_id
                    HAVING COUNT(file_id) > 0

                    UNION

                    SELECT %s
                    FROM PLAYLIST
                    LEFT JOIN PLAYLIST_TRACK ON PLAYLIST.id = PLAYLIST_TRACK.playlist_id
                    LEFT JOIN USER ON USER.id = PLAYLIST.user_id
                    %s
                    GROUP BY PLAYLIST.id
                )
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
                    SELECT COALESCE(COUNT (DISTINCT FF.user_id), 0) AS total
                    FROM FILE_FAVORITE FF
                    WHERE FF.user_id = :user_id
                    AND :uuid_zero IS NOT NULL
                    UNION
                    SELECT COUNT(PLAYLIST.id) AS total
                    FROM PLAYLIST
                    %s
                )
            ",
            $browser->getQueryCountFields(),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null
        );
        $data = $browser->launch($query, $queryCount);

        return ($data);
    }

    public static function getTrackIds(\aportela\DatabaseWrapper\DB $dbh, string $id): array
    {
        $query = "";
        $params = [];
        if ($id == \Spieldose\Playlist::FAVORITE_TRACKS_PLAYLIST_ID) {
            $query = "
                SELECT F.id
                FROM FILE_FAVORITE FF
                INNER JOIN FILE F ON F.ID = FF.file_id
                WHERE FF.user_id = :user_id
                ORDER BY FF.favorited
            ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId());
        } else {
            $query = "
                    SELECT F.id
                    FROM PLAYLIST_TRACK PT
                    INNER JOIN FILE F ON F.ID = PT.track_id
                    WHERE PT.playlist_id = :playlist_id
                    ORDER BY PT.track_index
                ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $id);
        }
        $data = $dbh->query($query, $params);
        $ids = [];
        foreach ($data as $item) {
            $ids[] = $item->id;
        }
        return ($ids);
    }
}

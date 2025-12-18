<?php

declare(strict_types=1);

namespace Spieldose\Entities\PlayList;

final class PlayList
{
    public string $id;
    public string $name;
    public int $createdAt;
    public int|null $updatedAt;
    /**
     * var array<\Spieldose\Entities\File>
     */
    public array $items;

    public \Spieldose\Entities\PlayList\PlayListFlags $flags;

    public function __construct(string $id, string $name)
    {
        if (mb_strlen($id) !== 36) {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
        if (mb_strlen($name) > 128) {
            throw new \Spieldose\Exception\InvalidParamsException("name");
        }
        $this->id = $id;
        $this->name = $name;
        $this->items = [];
        $this->flags = new \Spieldose\Entities\PlayList\PlayListFlags(false, false, false, false, false, false);
    }

    public function add(\aportela\DatabaseWrapper\DB $dbh, string $userId): bool
    {
        if (mb_strlen($userId) !== 36) {
            throw new \Spieldose\Exception\InvalidParamsException("userId");
        }
        $this->createdAt = intval(microtime(true) * 1000);
        $this->flags = new \Spieldose\Entities\PlayList\PlayListFlags(true, true, true, false, false, false);
        if ($dbh->execute(
            "
                INSERT INTO PLAYLIST
                    (id, name, user_id, ctime, mtime)
                VALUES
                    (:id, :name, :user_id, :ctime, NULL)
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name),
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", $this->createdAt)
            ]
        )) {
            if ($dbh->execute(
                "
                    INSERT INTO USER_PLAYLIST
                        (user_id, playlist_id, opened, actived, published, shared, favorites)
                    VALUES
                        (:user_id, :playlist_id, :opened, :actived, :published, :shared, :favorites)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                    $this->flags->opened ? new \aportela\DatabaseWrapper\Param\IntegerParam(":opened", $this->createdAt) : new \aportela\DatabaseWrapper\Param\NullParam(":opened"),
                    $this->flags->opened ? new \aportela\DatabaseWrapper\Param\IntegerParam(":actived", $this->createdAt) : new \aportela\DatabaseWrapper\Param\NullParam(":actived"),
                    $this->flags->published ? new \aportela\DatabaseWrapper\Param\IntegerParam(":published", $this->createdAt) : new \aportela\DatabaseWrapper\Param\NullParam(":published"),
                    $this->flags->shared ? new \aportela\DatabaseWrapper\Param\IntegerParam(":shared", $this->createdAt) : new \aportela\DatabaseWrapper\Param\NullParam(":shared"),
                    $this->flags->isFavorites ? new \aportela\DatabaseWrapper\Param\IntegerParam(":favorites", $this->createdAt) : new \aportela\DatabaseWrapper\Param\NullParam(":favorites"),
                ]
            )) {

                return ($dbh->execute(
                    "
                        UPDATE USER_PLAYLIST
                            SET actived = NULL
                        WHERE
                            user_id = :user_id
                        AND
                            playlist_id <> :playlist_id
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                    ]
                ));
            } else {
                return (false);
            }
        } else {
            return (false);
        }
    }

    public function delete(\aportela\DatabaseWrapper\DB $dbh, string $userId): bool
    {
        if (mb_strlen($userId) !== 36) {
            throw new \Spieldose\Exception\InvalidParamsException("userId");
        }
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
            new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId),
        ];
        if ($dbh->execute(
            "
                DELETE
                    FROM USER_PLAYLIST
                WHERE
                    playlist_id = :playlist_id
                AND
                    user_id = :user_id

            ",
            $params
        )) {
            return ($dbh->execute(
                "
                DELETE
                    FROM PLAYLIST
                WHERE
                    id = :playlist_id
                AND
                    user_id = :user_id
            ",
                $params
            ));
            // TODO: set another current active playlist (default = 0 ?)
        } else {
            return (false);
        }
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh, string $userId): void
    {
        $results = $dbh->query(
            "
                SELECT
                    P.name, P.ctime, P.mtime, P.user_id AS userId, UP.opened, UP.actived, UP.published, UP.shared
                FROM PLAYLIST P
                INNER JOIN USER_PLAYLIST UP ON UP.playlist_id = P.id AND UP.user_id = P.user_id
                WHERE
                    P.id = :id
                UNION
                SELECT
                    P.name, P.ctime AS createdAt, P.mtime AS updatedAt, P.user_id AS userId, NULL AS opened, NULL AS actived, NULL AS published, NULL AS shared
                FROM PLAYLIST P
                INNER JOIN USER_PLAYLIST UP ON UP.playlist_id = P.id AND UP.shared IS NOT NULL
                WHERE
                    P.id = :id
                AND
                    P.user_id <> P.id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id)
            ]
        );
        if (count($results) === 1) {
            $this->name = $results[0]->name;
            $this->createdAt = intval($results[0]->ctime);
            $this->updatedAt = is_numeric($results[0]->mtime) ? intval($results[0]->mtime) : null;
            $this->items = \Spieldose\Entities\PlayList\PlayListFileItem::getPlayListFileItems($dbh, $this->id);
            $this->flags->isMine = $userId == $results[0]->userId;
            $this->flags->opened = is_numeric($results[0]->opened);
            $this->flags->actived = is_numeric($results[0]->actived);
            $this->flags->published = is_numeric($results[0]->published);
            $this->flags->shared = is_numeric($results[0]->shared);
            $this->flags->isFavorites = $this->flags->isMine && $this->id === $userId; // favorites playlist has same uuid of the user
        } else {
            throw new \Spieldose\Exception\NotFoundException("id");
        }
    }

    public function open(\aportela\DatabaseWrapper\DB $dbh, string $userId): bool
    {
        if ($dbh->execute(
            "
                INSERT INTO USER_PLAYLIST
                    (user_id, playlist_id, opened, actived, published, shared, favorites)
                VALUES
                    (:user_id, :playlist_id, :current_timestamp, :current_timestamp NULL, NULL, NULL)
                ON CONFLICT (user_id, playlist_id) DO
                UPDATE
                    SET
                        :opened = :current_timestamp,
                        :actived = :current_timestamp,
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId),
                new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
            ]
        )) {
            return ($dbh->execute(
                "
                    UPDATE USER_PLAYLIST
                        SET actived = NULL
                    WHERE
                        user_id = :user_id
                    AND
                        playlist_id <> :playlist_id
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                ]
            ));
        } else {
            return (false);
        }
    }

    public function close(\aportela\DatabaseWrapper\DB $dbh, string $userId): bool
    {
        if ($dbh->execute(
            "
                UPDATE
                    USER_PLAYLIST
                SET
                    opened = NULL,
                    actived = NULL
                WHERE
                    user_id = :user_id
                AND
                    playlist_id = :playlist_id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId),
                new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
            ]
        )) {
            // if playlist was not saved (temporal playlist, delete after closing)
            return ($dbh->execute(
                "
                    DELETE
                        FROM USER_PLAYLIST
                    WHERE
                        user_id = :user_id
                    AND
                        playlist_id = :playlist_id
                    AND
                        published IS NULL
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                ]
            ));
            // TODO: remove playlist && details
        } else {
            return (false);
        }
        // TODO: set another current active playlist (default = 0 ?)
    }

    public function randomFill(\aportela\DatabaseWrapper\DB $dbh, int $count): bool
    {
        $dbh->execute(
            "
                DELETE
                FROM PLAYLIST_FILE
                WHERE
                    playlist_id = :playlist_id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
            ]
        );
        $dbh->execute(
            "
                INSERT INTO
                    PLAYLIST_FILE
                SELECT
                    :playlist_id, FILE.id, ROWID
                FROM FILE
                ORDER BY RANDOM()
                LIMIT :count
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":count", $count),
            ]
        );
        return (true);
    }

    public function toggleFavoriteFile(\aportela\DatabaseWrapper\DB $dbh, string $userId, string $fileId, bool $flag): bool
    {
        if ($fileId !== '' && $fileId !== '0') {
            $query = null;
            $params = [
                new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $fileId),
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId),
            ];
            if ($flag) {
                $query = "
                    INSERT INTO FILE_FAVORITE
                        (file_id, user_id, ftime)
                    VALUES
                        (:file_id, :user_id, :current_timestamp)
                    ON CONFLICT (file_id, user_id) DO
                    UPDATE SET
                        ftime = :ftime
                ";
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":ftime", intval(microtime(true) * 1000));
            } else {
                $query = "
                    DELETE
                    FROM FILE_FAVORITE
                    WHERE
                        file_id = :file_id
                    AND
                        user_id = :user_id
                ";
            }
            return ($dbh->execute($query, $params));
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public static function getCurrentPlayLists(\aportela\DatabaseWrapper\DB $dbh, string $userId): array
    {
        $results = $dbh->query(
            "
                SELECT
                    P.id, P.name, P.ctime AS createdAt, P.mtime AS updatedAt, P.user_id AS userId, UP.opened, UP.actived, UP.published, UP.shared
                FROM USER_PLAYLIST UP
                INNER JOIN PLAYLIST P ON P.id = UP.playlist_id
                WHERE
                    UP.user_id = :user_id
                AND
                    UP.opened IS NOT NULL
                ORDER BY UP.opened
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId)
            ]
        );
        $playLists = [];
        foreach ($results as $result) {
            $playList = new \Spieldose\Entities\PlayList\PlayList($result->id, $result->name);
            $playList->flags->isMine = $userId == $result->userId;
            $playList->flags->opened = is_numeric($result->opened);
            $playList->flags->actived = is_numeric($result->actived);
            $playList->flags->published = is_numeric($result->published);
            $playList->flags->shared = is_numeric($result->shared);
            $playList->flags->isFavorites = false;
            $playList->items = \Spieldose\Entities\PlayList\PlayListFileItem::getPlayListFileItems($dbh, $playList->id);
            $playLists[] = $playList;
        }
        return ($playLists);
    }
}

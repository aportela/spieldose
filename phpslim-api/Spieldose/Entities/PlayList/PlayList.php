<?php

declare(strict_types=1);

namespace Spieldose\Entities\PlayList;

final class PlayList
{
    public const int MAX_PLAYLIST_NAME_LENGTH = 128;
    public string $id;
    public string $name;
    public int $createdAtTimestamp;
    public int|null $updatedAtTimestamp;
    public int |null $currentItemIndex;
    public int |null $currentItemPosition;

    /**
     * TODO: allow another entities (stream/radiostation ?)
     * var array<\Spieldose\Entities\File>
     */
    public array $items;

    public \Spieldose\Entities\PlayList\PlayListFlags $flags;

    public function __construct(string $id, string $name, \Spieldose\Entities\PlayList\PlayListFlags|null $flags = null)
    {
        if (mb_strlen($id) !== 36) {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
        if (mb_strlen($name) > self::MAX_PLAYLIST_NAME_LENGTH) {
            throw new \Spieldose\Exception\InvalidParamsException("name");
        }
        $this->id = $id;
        $this->name = $name;
        $this->items = [];
        $this->flags = $flags ?? new \Spieldose\Entities\PlayList\PlayListFlags(false, false, null, null, null, null);
        $this->currentItemIndex = null;
        $this->currentItemPosition = null;
    }

    /**
     * @param $items array<\Spieldose\Entities\File>
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    private function isOwnedByUser(\aportela\DatabaseWrapper\DB $dbh, string $userId): bool
    {
        $results = $dbh->query(
            "
                SELECT
                    NULL
                FROM PLAYLIST P
                WHERE
                    P.id = :id
                AND
                    P.user_id = :user_id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId)
            ]
        );
        return (count($results) === 1);
    }

    private function getPlayListUserData(\aportela\DatabaseWrapper\DB $dbh, string $userId): bool
    {
        $results = $dbh->query(
            "
                SELECT
                    P.user_id AS ownerId, UP.opened AS openedAtTimestamp, UP.actived AS activedAtTimestamp, UP.published AS publishedAtTimestamp, UP.shared AS sharedAtTimestamp, UP.playlist_item_index as playListItemIndex, UP.playlist_item_position as playListItemPosition
                FROM USER_PLAYLIST UP
                INNER JOIN PLAYLIST P ON P.id = UP.playlist_id
                WHERE
                    UP.playlist_id = :playlist_id
                AND
                    P.user_id = :user_id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId)
            ]
        );
        if (count($results) === 1) {
            $this->flags->setFlags(
                $results[0]->ownerId === $userId,
                $this->id === $userId,
                is_numeric($results[0]->openedAtTimestamp)  ? intval($results[0]->openedAtTimestamp) : null,
                is_numeric($results[0]->activedAtTimestamp) ? intval($results[0]->activedAtTimestamp) : null,
                is_numeric($results[0]->publishedAtTimestamp) ?  intval($results[0]->publishedAtTimestamp) : null,
                is_numeric($results[0]->sharedAtTimestamp) ? intval($results[0]->sharedAtTimestamp) : null,
            );
            $this->currentItemIndex = is_numeric($results[0]->playListItemIndex) ? intval($results[0]->playListItemIndex) : null;
            $this->currentItemPosition = is_numeric($results[0]->playListItemPosition) ? intval($results[0]->playListItemPosition) : null;
            return (true);
        } else {
            return (false);
        }
    }

    private function associatePlayListFlagsToUser(\aportela\DatabaseWrapper\DB $dbh, string $userId): void
    {
        $dbh->execute(
            "
                INSERT INTO USER_PLAYLIST
                    (user_id, playlist_id, opened, actived, published, shared, playlist_item_index, playlist_item_position)
                VALUES
                    (:user_id, :playlist_id, :opened, :actived, :published, :shared, :playlist_item_index, :playlist_item_position)
                ON CONFLICT (user_id, playlist_id) DO
                UPDATE SET
                    opened = :opened,
                    actived = :actived,
                    published = :published,
                    shared = :shared,
                    playlist_item_index = :playlist_item_index,
                    playlist_item_position = :playlist_item_position
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId),
                new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                $this->flags->isOpened ? new \aportela\DatabaseWrapper\Param\IntegerParam(":opened", $this->flags->getOpenedAtTimestamp()) : new \aportela\DatabaseWrapper\Param\NullParam(":opened"),
                $this->flags->isActive ? new \aportela\DatabaseWrapper\Param\IntegerParam(":actived", $this->flags->getActivedAtTimestamp()) : new \aportela\DatabaseWrapper\Param\NullParam(":actived"),
                $this->flags->isPublished ? new \aportela\DatabaseWrapper\Param\IntegerParam(":published", $this->flags->getPublishedAtTimestamp()) : new \aportela\DatabaseWrapper\Param\NullParam(":published"),
                $this->flags->isShared ? new \aportela\DatabaseWrapper\Param\IntegerParam(":shared", $this->flags->getSharedAtTimestamp()) : new \aportela\DatabaseWrapper\Param\NullParam(":shared"),
                $this->currentItemIndex === null ? new \aportela\DatabaseWrapper\Param\NullParam(":playlist_item_index") : new \aportela\DatabaseWrapper\Param\IntegerParam(":playlist_item_index", $this->currentItemIndex),
                $this->currentItemPosition === null ? new \aportela\DatabaseWrapper\Param\NullParam(":playlist_item_position") : new \aportela\DatabaseWrapper\Param\IntegerParam(":playlist_item_position", $this->currentItemPosition)
            ]
        );
    }

    private function resetActiveFlagOnAnotherUserPlayLists(\aportela\DatabaseWrapper\DB $dbh, string $userId): void
    {
        $dbh->execute(
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
        );
    }

    public function add(\aportela\DatabaseWrapper\DB $dbh, string $userId, bool $isActived = true): void
    {
        if (mb_strlen($userId) !== 36) {
            throw new \Spieldose\Exception\InvalidParamsException("userId");
        }
        $this->createdAtTimestamp = intval(microtime(true) * 1000);
        $this->flags->setFlags(
            true, // isMine (i am the creator... so YES)
            false, // favorites playlist can not be created manually (favorites playlist id === userId)
            $this->createdAtTimestamp, // opened (new playlist is created & opened)
            $isActived ? $this->createdAtTimestamp : null, // actived (new playlist is created & set to active)
            null, // published (new playlist, created & opened, is always "temporal", until real save action)
            null, // shared (new playlist, not published, can not be shared)
        );
        try {
            $dbh->beginTransaction();
            // create playlist
            $dbh->execute(
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
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", $this->createdAtTimestamp)
                ]
            );
            // associate playlist to user with flags (opened & active)
            $this->associatePlayListFlagsToUser($dbh, $userId);
            // only one playlist can be active, clear active flag on other playlists of this user
            $this->resetActiveFlagOnAnotherUserPlayLists($dbh, $userId);
            $dbh->commit();
        } catch (\aportela\DatabaseWrapper\Exception\DBException $e) {
            $dbh->rollBack();
            throw $e;
        }
    }

    public function update(\aportela\DatabaseWrapper\DB $dbh, string $userId): void
    {
        if (mb_strlen($userId) !== 36) {
            throw new \Spieldose\Exception\InvalidParamsException("userId");
        }
        $this->updatedAtTimestamp = intval(microtime(true) * 1000);
        try {
            $dbh->beginTransaction();
            if ($this->isOwnedByUser($dbh, $userId)) {
                $dbh->execute(
                    "
                    UPDATE PLAYLIST
                    SET
                        name = :name,
                        mtime = :mtime
                    WHERE
                        id = :id
                    AND
                        user_id = :user_id
                ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $this->updatedAtTimestamp),
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                        new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId)
                    ]
                );
            }
            // associate playlist to user with flags (opened & active)
            $this->associatePlayListFlagsToUser($dbh, $userId);
            if ($this->flags->actived) {
                // only one playlist can be active, clear active flag on other playlists of this user
                $this->resetActiveFlagOnAnotherUserPlayLists($dbh, $userId);
            }
            $dbh->commit();
        } catch (\aportela\DatabaseWrapper\Exception\DBException $e) {
            $dbh->rollBack();
            throw $e;
        }
    }

    public function delete(\aportela\DatabaseWrapper\DB $dbh, string $userId): void
    {
        if (mb_strlen($userId) !== 36) {
            throw new \Spieldose\Exception\InvalidParamsException("userId");
        }
        try {
            $dbh->beginTransaction();
            $params = [
                new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId),
            ];
            $dbh->execute(
                "
                    DELETE
                        FROM USER_PLAYLIST
                    WHERE
                        playlist_id = :playlist_id
                    AND
                        user_id = :user_id

                ",
                $params
            );
            if ($this->isOwnedByUser($dbh, $userId)) {
                $dbh->execute(
                    "
                            DELETE
                                FROM PLAYLIST
                            WHERE
                                id = :playlist_id
                            AND
                                user_id = :user_id
                        ",
                    $params
                );
            }
            $dbh->commit();
        } catch (\aportela\DatabaseWrapper\Exception\DBException $e) {
            $dbh->rollBack();
            throw $e;
        }
    }

    public function open(\aportela\DatabaseWrapper\DB $dbh, string $userId): void
    {
        $this->getPlayListUserData($dbh, $userId);
        $this->flags->opened = true;
        $this->flags->actived = true;
        try {
            $dbh->beginTransaction();
            // associate playlist to user with flags (opened & active)
            $this->associatePlayListFlagsToUser($dbh, $userId);
            // only one playlist can be active, clear active flag on other playlists of this user
            $this->resetActiveFlagOnAnotherUserPlayLists($dbh, $userId);
            $dbh->commit();
        } catch (\aportela\DatabaseWrapper\Exception\DBException $e) {
            $dbh->rollBack();
            throw $e;
        }
    }

    public function close(\aportela\DatabaseWrapper\DB $dbh, string $userId): void
    {
        $this->getPlayListUserData($dbh, $userId);
        $this->flags->close();
        $this->flags->unsetActive();
        try {
            $dbh->beginTransaction();
            // associate playlist to user with flags (opened & active)
            $this->associatePlayListFlagsToUser($dbh, $userId);
            // clear active flag on other playlists of this user
            $this->resetActiveFlagOnAnotherUserPlayLists($dbh, $userId);
            $dbh->commit();
        } catch (\aportela\DatabaseWrapper\Exception\DBException $e) {
            $dbh->rollBack();
            throw $e;
        }
    }

    public function randomFill(\aportela\DatabaseWrapper\DB $dbh, int $count, string $userId): void
    {
        if ($this->isOwnedByUser($dbh, $userId)) {
            try {
                $dbh->beginTransaction();
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
                $dbh->execute(
                    "
                        UPDATE PLAYLIST
                        SET
                            mtime = :mtime
                        WHERE
                            id = :id
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", intval(microtime(true) * 1000)),
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                    ]
                );
                $this->getPlayListUserData($dbh, $userId);
                // associate playlist to user with flags (opened & active)
                $this->getPlayListUserData($dbh, $userId);
                $this->currentItemIndex = 0;
                $this->currentItemPosition = null;
                $this->associatePlayListFlagsToUser($dbh, $userId);
                $dbh->commit();
            } catch (\aportela\DatabaseWrapper\Exception\DBException $e) {
                $dbh->rollBack();
                throw $e;
            }
        } else {
            throw new \Spieldose\Exception\AccessDeniedException("");
        }
    }

    public function empty(\aportela\DatabaseWrapper\DB $dbh, string $userId): void
    {
        if ($this->isOwnedByUser($dbh, $userId)) {
            try {
                $dbh->beginTransaction();
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
                        UPDATE PLAYLIST
                        SET
                            mtime = :mtime
                        WHERE
                            id = :id
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", intval(microtime(true) * 1000)),
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                    ]
                );
                $dbh->commit();
            } catch (\aportela\DatabaseWrapper\Exception\DBException $e) {
                $dbh->rollBack();
                throw $e;
            }
        } else {
            throw new \Spieldose\Exception\AccessDeniedException("");
        }
    }

    public function setCurrentItemIndex(\aportela\DatabaseWrapper\DB $dbh, int $currentItemIndex, string $userId): void
    {
        $this->getPlayListUserData($dbh, $userId);
        $currentTimestamp = intval(microtime(true) * 1000);
        if (! $this->flags->isOpened) {
            $this->flags->open($currentTimestamp);
        }
        if (! $this->flags->isActive) {
            $this->flags->setActive($currentTimestamp);
        }
        $this->currentItemIndex = $currentItemIndex;
        $this->currentItemPosition = 0;
        try {
            $dbh->beginTransaction();
            // associate playlist to user with flags (opened & active)
            $this->associatePlayListFlagsToUser($dbh, $userId);
            // only one playlist can be active, clear active flag on other playlists of this user
            $this->resetActiveFlagOnAnotherUserPlayLists($dbh, $userId);
            $dbh->commit();
        } catch (\aportela\DatabaseWrapper\Exception\DBException $e) {
            $dbh->rollBack();
            throw $e;
        }
    }

    public static function toggleFavoriteFile(\aportela\DatabaseWrapper\DB $dbh, string $userId, string $fileId, bool $flag): void
    {
        if ($fileId !== '' && $fileId !== '0') {
            try {
                $dbh->beginTransaction();
                // always create / update user favorites playlist (playlist id === userId)
                $dbh->execute(
                    "
                        INSERT INTO PLAYLIST
                            (id, name, user_id, ctime, mtime)
                        VALUES
                            (:id, :name, :user_id, :current_timestamp, NULL)
                        ON CONFLICT (id) DO
                        UPDATE SET
                            mtime = :current_timestamp
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $userId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":name", "favorites"),
                        new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
                    ]
                );
                $query = null;
                $params = [
                    new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $fileId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $userId),
                ];
                if ($flag) {
                    $query = "
                        INSERT INTO PLAYLIST_FILE
                            (playlist_id, file_id, file_index)
                        VALUES
                            (:playlist_id, :file_id, :current_timestamp)
                        ON CONFLICT (playlist_id, file_id, file_index) DO
                        UPDATE SET
                            file_index = :current_timestamp
                    ";
                    $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000));
                } else {
                    $query = "
                        DELETE
                        FROM PLAYLIST_FILE
                        WHERE
                            playlist_id = :playlist_id
                        AND
                            file_id = :file_id
                    ";
                }
                $dbh->execute($query, $params);
                $dbh->commit();
            } catch (\aportela\DatabaseWrapper\Exception\DBException $e) {
                $dbh->rollBack();
                throw $e;
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("fileId");
        }
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh, string $userId): void
    {
        $results = $dbh->query(
            "
                SELECT
                    P.user_id AS ownerId, P.name, P.ctime, P.mtime, UP.opened AS openedAtTimestamp, UP.actived AS activedAtTimestamp, UP.published AS publishedAtTimestamp, UP.shared AS sharedAtTimestamp,
                    UP.playlist_item_index as playListItemIndex, UP.playlist_item_position as playListItemPosition
                FROM PLAYLIST P
                LEFT JOIN USER_PLAYLIST UP ON UP.playlist_id = P.id AND UP.user_id = :user_id
                WHERE
                    P.id = :id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId)
            ]
        );
        if (count($results) === 1) {
            $this->name = $results[0]->name;
            $this->createdAtTimestamp = is_numeric($results[0]->ctime) ? intval($results[0]->ctime) : 0;
            $this->updatedAtTimestamp = is_numeric($results[0]->mtime) ? intval($results[0]->mtime) : null;
            $this->flags->setFlags(
                $userId === $results[0]->ownerId, // is Mine ?
                $this->id === $userId, // is user favorites playlist (playlist id === userId)
                is_numeric($results[0]->openedAtTimestamp)  ? intval($results[0]->openedAtTimestamp) : null,
                is_numeric($results[0]->activedAtTimestamp) ? intval($results[0]->activedAtTimestamp) : null,
                is_numeric($results[0]->publishedAtTimestamp) ?  intval($results[0]->publishedAtTimestamp) : null,
                is_numeric($results[0]->sharedAtTimestamp) ? intval($results[0]->sharedAtTimestamp) : null,
            );
            $this->currentItemIndex = is_numeric($results[0]->playListItemIndex) ? intval($results[0]->playListItemIndex) : null;
            $this->currentItemPosition = is_numeric($results[0]->playListItemPosition) ? intval($results[0]->playListItemPosition) : null;
            $this->items = \Spieldose\Entities\PlayList\PlayListFileItem::getPlayListFileItems($dbh, $this->id, $userId);
        } else {
            throw new \Spieldose\Exception\NotFoundException("id");
        }
    }

    /**
     * return array<Spieldose\Entities\PlayList\PlayList>
     */
    public static function getCurrentPlayLists(\aportela\DatabaseWrapper\DB $dbh, string $userId): array
    {
        $results = $dbh->query(
            "
                SELECT
                    P.id, P.name, P.ctime AS createdAtTimestamp, P.mtime AS updatedAtTimestamp, P.user_id AS ownerId,
                    UP.opened AS openedAtTimestamp, UP.actived AS activedAtTimestamp, UP.published AS publishedAtTimestamp, UP.shared AS sharedAtTimestamp,
                    UP.playlist_item_index AS playListItemIndex, UP.playlist_item_position AS playListItemPosition
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
            $playList = new \Spieldose\Entities\PlayList\PlayList(
                $result->id,
                $result->name,
                new \Spieldose\Entities\PlayList\PlayListFlags(
                    $result->ownerId === $userId,
                    $result->id === $userId,
                    is_numeric($result->openedAtTimestamp)  ? intval($result->openedAtTimestamp) : null,
                    is_numeric($result->activedAtTimestamp) ? intval($result->activedAtTimestamp) : null,
                    is_numeric($result->publishedAtTimestamp) ? intval($result->publishedAtTimestamp) : null,
                    is_numeric($result->sharedAtTimestamp) ? intval($result->sharedAtTimestamp) : null
                )
            );
            $playList->currentItemIndex = is_numeric($result->playListItemIndex) ? intval($result->playListItemIndex) : null;
            $playList->currentItemPosition = is_numeric($result->playListItemPosition) ? intval($result->playListItemPosition) : null;
            $playList->setItems(\Spieldose\Entities\PlayList\PlayListFileItem::getPlayListFileItems($dbh, $playList->id, $userId));
            $playLists[] = $playList;
        }
        return ($playLists);
    }
}

<?php

declare(strict_types=1);

namespace Spieldose;

class NewPlayList
{
    public string $id;
    public string $name;
    public int $createdAt;
    public int|null $updatedAt;
    /**
     * var array<\Spieldose\Entities\File>
     */
    public array $items;

    public \Spieldose\NewPlayListFlags  $flags;

    public function __construct(string $id, string $name)
    {
        if (mb_strlen($id) !== 36) {
            throw new \Spieldose\Exception\InvalidParamsException("userId");
        }
        if (mb_strlen($name) > 128) {
            throw new \Spieldose\Exception\InvalidParamsException("name");
        }
        $this->id = $id;
        $this->name = $name;
        $this->items = [];
        $this->flags = new \Spieldose\NewPlayListFlags(false, false, false, false, false);
    }

    public function add(\aportela\DatabaseWrapper\DB $dbh, string $userId): bool
    {
        if (mb_strlen($userId) !== 36) {
            throw new \Spieldose\Exception\InvalidParamsException("userId");
        }
        $this->createdAt = intval(microtime(true) * 1000);
        $this->flags = new \Spieldose\NewPlayListFlags(true, true, false, false, false);
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
            return ($dbh->execute(
                "
                INSERT INTO USER_PLAYLIST
                    (user_id, playlist_id, opened, published, shared)
                VALUES
                    (:user_id, :playlist_id, :opened, :published, :shared)
            ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                    $this->flags->opened ? new \aportela\DatabaseWrapper\Param\IntegerParam(":opened", $this->createdAt) : new \aportela\DatabaseWrapper\Param\NullParam(":opened"),
                    $this->flags->published ? new \aportela\DatabaseWrapper\Param\IntegerParam(":published", $this->createdAt) : new \aportela\DatabaseWrapper\Param\NullParam(":published"),
                    $this->flags->shared ? new \aportela\DatabaseWrapper\Param\IntegerParam(":shared", $this->createdAt) : new \aportela\DatabaseWrapper\Param\NullParam(":shared"),
                ]
            ));
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
        } else {
            return (false);
        }
    }

    public static function getCurrentPlayLists(\aportela\DatabaseWrapper\DB $dbh, string $userId): array
    {
        $results = $dbh->query(
            "
                SELECT
                    P.id, P.name, P.ctime AS createdAt, P.mtime AS updatedAt, P.user_id AS userId, UP.opened, UP.published, UP.shared
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
            $playList = new \Spieldose\NewPlayList($result->id, $result->name);
            $playList->flags->isMine = $userId == $result->userId;
            $playList->flags->opened = is_numeric($result->opened);
            $playList->flags->published = is_numeric($result->published);
            $playList->flags->shared = is_numeric($result->shared);
            $playList->flags->isFavorites = false;
            $playList->items = \Spieldose\Entities\File::getRandomPlayList($dbh, 32);
            $playLists[] = $playList;
        }
        return ($playLists);
    }
}

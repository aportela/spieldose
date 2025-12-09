<?php

declare(strict_types=1);

namespace Spieldose;

class NewPlayList
{
    public string $id;
    public string $name;
    public int $createdAt;
    public int|null $updatedAt;

    private bool $flagOpened;
    private bool $flagPublished;
    private bool $flagShared;
    private bool $flagIsFavorites;
    private bool $flagIsMine;

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
    }

    public function add(\aportela\DatabaseWrapper\DB $dbh, string $userId): bool
    {
        if (mb_strlen($userId) !== 36) {
            throw new \Spieldose\Exception\InvalidParamsException("userId");
        }
        $this->createdAt = intval(microtime(true) * 1000);
        $this->flagOpened = true;
        $this->flagPublished = false;
        $this->flagShared = false;
        $this->flagIsFavorites = false;
        $this->flagIsMine = true;
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
                    $this->flagOpened ? new \aportela\DatabaseWrapper\Param\IntegerParam(":opened", $this->createdAt) : new \aportela\DatabaseWrapper\Param\NullParam(":opened"),
                    $this->flagPublished ? new \aportela\DatabaseWrapper\Param\IntegerParam(":published", $this->createdAt) : new \aportela\DatabaseWrapper\Param\NullParam(":published"),
                    $this->flagShared ? new \aportela\DatabaseWrapper\Param\IntegerParam(":shared", $this->createdAt) : new \aportela\DatabaseWrapper\Param\NullParam(":shared"),
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

    public static function getCurrentPlayLists(\aportela\DatabaseWrapper\DB $db, string $userId): array
    {
        return ($db->query(
            "
                SELECT
                    P.id, P.name, P.ctime AS createdAt, P.mtime AS updatedAt
                FROM USER_PLAYLIST UP
                INNER JOIN PLAYLIST P ON P.id = UP.playlist_id AND P.user_id = UP.user_id
                WHERE
                    UP.user_id = :user_id
                AND
                    UP.opened IS NOT NULL
                ORDER BY UP.opened
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId)
            ],
        ));
    }
}

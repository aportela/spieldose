<?php

declare(strict_types=1);

namespace Spieldose;

class NewPlayList
{
    public string $id;
    public string $name;

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
                new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", intval(microtime(true) * 1000))
            ]
        )) {
            return ($dbh->execute(
                "
                INSERT INTO USER_PLAYLIST
                    (user_id, playlist_id, opened, published, shared)
                VALUES
                    (:user_id, :playlist_id, :opened, NULL, NULL)
            ",
                [

                    new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $this->id),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":opened", intval(microtime(true) * 1000))
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
}

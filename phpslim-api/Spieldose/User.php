<?php

declare(strict_types=1);

namespace Spieldose;

class User
{
    public ?string $id;
    public ?string $email;
    public ?string $password;
    public ?string $passwordHash;
    public ?string $name;
    public ?int $deletedTimestamp;

    public function __construct(string $id = "", string $email = "", string $password = "", string $name = "")
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
    }

    private function passwordHash(string $password = ""): string
    {
        return (password_hash($password, PASSWORD_BCRYPT, array('cost' => 12)));
    }

    public function add(\aportela\DatabaseWrapper\DB $dbh): void
    {
        if (!empty($this->id) && mb_strlen($this->id) == 36) {
            if (!empty($this->email) && mb_strlen($this->email) <= 255 && filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                if (!empty($this->name) && mb_strlen($this->name) <= 32) {
                    if (!empty($this->password)) {
                        $params = array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":password_hash", $this->passwordHash($this->password)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":name", mb_strtolower($this->name))
                        );
                        $dbh->exec(" INSERT INTO USER (id, email, password_hash, name, ctime, mtime, dtime) VALUES(:id, :email, :password_hash, :name, strftime('%s', 'now'), strftime('%s', 'now'), NULL) ", $params);
                    } else {
                        throw new \Spieldose\Exception\InvalidParamsException("password");
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("name");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("email");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function update(\aportela\DatabaseWrapper\DB $dbh): void
    {
        if (!empty($this->id) && mb_strlen($this->id) == 36) {
            if (!empty($this->email) && mb_strlen($this->email) <= 255 && filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                if (!empty($this->name) && mb_strlen($this->name) <= 32) {
                    if (!empty($this->password)) {
                        $params = array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":password_hash", $this->passwordHash($this->password)),
                            new \aportela\DatabaseWrapper\Param\StringParam(":name", mb_strtolower($this->name))
                        );
                        $dbh->exec(" UPDATE USER SET email = :email, password_hash = :password_hash, name = :name, mtime = strftime('%s', 'now') WHERE id = :id ", $params);
                    } else {
                        throw new \Spieldose\Exception\InvalidParamsException("password");
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("name");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("email");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function updateProfile(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $this->id = \Spieldose\UserSession::getUserId();
        if (!empty($this->email) && mb_strlen($this->email) <= 255 && filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            if (!self::isEmailUsedByAnotherUser($dbh, $this->email)) {
                if (!empty($this->name) && mb_strlen($this->name) <= 32) {
                    if (!self::isNameUsedByAnotherUser($dbh, $this->name)) {
                        if (!empty($this->password)) {
                            $params = array(
                                new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id)),
                                new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email)),
                                new \aportela\DatabaseWrapper\Param\StringParam(":password_hash", $this->passwordHash($this->password)),
                                new \aportela\DatabaseWrapper\Param\StringParam(":name", mb_strtolower($this->name))
                            );
                            $dbh->exec(" UPDATE USER SET email = :email, password_hash = :password_hash, name = :name, mtime = strftime('%s', 'now') WHERE id = :id ", $params);
                        } else {
                            $params = array(
                                new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id)),
                                new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email)),
                                new \aportela\DatabaseWrapper\Param\StringParam(":name", mb_strtolower($this->name))
                            );
                            $dbh->exec(" UPDATE USER SET email = :email, name = :name, mtime = strftime('%s', 'now') WHERE id = :id ", $params);
                        }
                        \Spieldose\UserSession::set($this->id, $this->email, $this->name);
                    } else {
                        throw new \Spieldose\Exception\AlreadyExistsException("name");
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("name");
                }
            } else {
                throw new \Spieldose\Exception\AlreadyExistsException("email");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("email");
        }
    }

    public function exists(\aportela\DatabaseWrapper\DB $dbh): bool
    {
        $exists = false;
        try {
            $this->get($dbh);
            $exists = true;
        } catch (\Throwable $e) {
        } finally {
            return ($exists);
        }
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $results = null;
        if (!empty($this->id) && mb_strlen($this->id) == 36) {
            $results = $dbh->query(
                "
                    SELECT
                        USER.id, USER.email, USER.password_hash AS passwordHash, USER.name, USER.dtime
                    FROM USER
                    WHERE USER.id = :id
                ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id))
                )
            );
        } elseif (!empty($this->email) && filter_var($this->email, FILTER_VALIDATE_EMAIL) && mb_strlen($this->email) <= 255) {
            $results = $dbh->query(
                "
                    SELECT
                        USER.id, USER.email, USER.password_hash AS passwordHash, USER.name, USER.dtime
                    FROM USER
                        WHERE USER.email = :email
                    ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email))
                )
            );
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id,email");
        }
        if (count($results) == 1) {
            $this->id = $results[0]->id;
            $this->email = $results[0]->email;
            $this->passwordHash = $results[0]->passwordHash;
            $this->name = $results[0]->name;
            $this->deletedTimestamp = $results[0]->dtime;
        } else {
            throw new \Spieldose\Exception\NotFoundException("");
        }
    }

    public static function isEmailUsed(\aportela\DatabaseWrapper\DB $dbh, string $email): bool
    {
        $results = null;
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && mb_strlen($email) <= 255) {
            $results = $dbh->query(
                "
                    SELECT
                        USER.id
                    FROM USER
                    WHERE USER.email = :email
                ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($email))
                )
            );
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("email");
        }
        return (count($results) == 1);
    }

    public static function isEmailUsedByAnotherUser(\aportela\DatabaseWrapper\DB $dbh, string $email): bool
    {
        $results = null;
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && mb_strlen($email) <= 255) {
            $results = $dbh->query(
                "
                    SELECT
                        USER.id
                    FROM USER
                    WHERE USER.email = :email
                    AND USER.id <> :id
                ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($email)),
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", \Spieldose\UserSession::getUserId())

                )
            );
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("email");
        }
        return (count($results) == 1);
    }

    public static function isNameUsed(\aportela\DatabaseWrapper\DB $dbh, string $name): bool
    {
        $results = null;
        if (!empty($name) && mb_strlen($name) <= 36) {
            $results = $dbh->query(
                "
                    SELECT
                        USER.id
                    FROM USER
                        WHERE USER.name = :name
                    ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", $name)
                )
            );
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("name");
        }
        return (count($results) == 1);
    }

    public static function isNameUsedByAnotherUser(\aportela\DatabaseWrapper\DB $dbh, string $name): bool
    {
        $results = null;
        if (!empty($name) && mb_strlen($name) <= 36) {
            $results = $dbh->query(
                "
                    SELECT
                        USER.id
                    FROM USER
                    WHERE USER.name = :name
                    AND USER.id <> :id
                ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", $name),
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", \Spieldose\UserSession::getUserId())
                )
            );
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("name");
        }
        return (count($results) == 1);
    }

    public function signIn(\aportela\DatabaseWrapper\DB $dbh): bool
    {
        if (!empty($this->password)) {
            $this->get($dbh);
            if (!$this->deletedTimestamp) {
                if (password_verify($this->password, $this->passwordHash)) {
                    \Spieldose\UserSession::set($this->id, $this->email, $this->name);
                    return (true);
                } else {
                    throw new \Spieldose\Exception\UnauthorizedException("password");
                }
            } else {
                throw new \Spieldose\Exception\DeletedException("");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("password");
        }
    }

    public static function signOut(): bool
    {
        \Spieldose\UserSession::clear();
        return (true);
    }
}

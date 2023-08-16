<?php

declare(strict_types=1);

namespace Spieldose;

class User
{
    public ?string $id;
    public ?string $email;
    public ?string $password;
    public ?string $passwordHash;

    public function __construct(string $id = "", string $email = "", string $password = "")
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
    }

    private function passwordHash(string $password = ""): string
    {
        return (password_hash($password, PASSWORD_BCRYPT, array('cost' => 12)));
    }

    public function add(\aportela\DatabaseWrapper\DB $dbh): void
    {
        if (!empty($this->id) && mb_strlen($this->id) == 36) {
            if (!empty($this->email) && mb_strlen($this->email) <= 255 && filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                if (!empty($this->password)) {
                    $params = array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id)),
                        new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email)),
                        new \aportela\DatabaseWrapper\Param\StringParam(":password_hash", $this->passwordHash($this->password))
                    );
                    $dbh->exec(" INSERT INTO USER (id, email, password_hash) VALUES(:id, :email, :password_hash) ", $params);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("password");
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
                if (!empty($this->password)) {
                    $params = array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id)),
                        new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email)),
                        new \aportela\DatabaseWrapper\Param\StringParam(":password_hash", $this->passwordHash($this->password))
                    );
                    $dbh->exec(" UPDATE USER SET email = :email, password_hash = :password_hash WHERE id = :id ", $params);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("password");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("email");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $results = null;
        if (!empty($this->id) && mb_strlen($this->id) == 36) {
            $results = $dbh->query(
                "
                        SELECT
                            USER.id, USER.email, USER.password_hash AS passwordHash
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
                        USER.id, USER.email, USER.password_hash AS passwordHash
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
            throw new \Spieldose\Exception\InvalidParamsException("id,email");
        }
        return (count($results) == 1);
    }

    public function signIn(\aportela\DatabaseWrapper\DB $dbh): bool
    {
        if (!empty($this->password)) {
            $this->get($dbh);
            if (password_verify($this->password, $this->passwordHash)) {
                \Spieldose\UserSession::set($this->id, $this->email);
                return (true);
            } else {
                throw new \Spieldose\Exception\UnauthorizedException("password");
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

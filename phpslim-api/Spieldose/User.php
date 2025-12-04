<?php

declare(strict_types=1);

namespace Spieldose;

class User
{
    public ?string $passwordHash = null;

    public function __construct(public ?string $id = "", public ?string $email = "", public ?string $password = "") {}

    private function passwordHash(string $password = ""): string
    {
        return (password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]));
    }

    /**
     * @return array<\aportela\DatabaseWrapper\Param\InterfaceParam>
     */
    private function validateAndPrepareParams(): array
    {
        if (in_array($this->id, [null, '', '0'], true) || mb_strlen($this->id) !== 36) {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }

        if (in_array($this->email, [null, '', '0'], true) || mb_strlen($this->email) > 255 || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \Spieldose\Exception\InvalidParamsException("email");
        }

        if (in_array($this->password, [null, '', '0'], true)) {
            throw new \Spieldose\Exception\InvalidParamsException("password");
        }

        return [
            new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id)),
            new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email)),
            new \aportela\DatabaseWrapper\Param\StringParam(":password_hash", $this->passwordHash($this->password)),
        ];
    }

    public function add(\aportela\DatabaseWrapper\DB $db): void
    {
        $params = $this->validateAndPrepareParams();
        $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", intval(microtime(true) * 1000));
        $db->execute(
            "
                INSERT INTO USER
                    (id, email, password_hash, ctime, mtime)
                VALUES
                    (:id, :email, :password_hash, :ctime, NULL)
            ",
            $params
        );
    }

    public function update(\aportela\DatabaseWrapper\DB $db): void
    {
        $params = $this->validateAndPrepareParams();
        $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", intval(microtime(true) * 1000));
        $db->execute(
            "
                UPDATE USER SET
                    email = :email,
                    password_hash = :password_hash,
                    mtime = :mtime
                WHERE
                    id = :id
            ",
            $params
        );
        if (ini_get("session.use_cookies") && PHP_SAPI !== 'cli' && is_string($this->email)) {
            \Spieldose\UserSession::setEmail($this->email);
        }
    }

    public function get(\aportela\DatabaseWrapper\DB $db): void
    {
        $results = [];
        if (!in_array($this->id, [null, '', '0'], true) && mb_strlen($this->id) === 36) {
            $results = $db->query(
                "
                    SELECT
                        USER.id, USER.email, USER.password_hash AS passwordHash
                    FROM USER
                    WHERE USER.id = :id
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", mb_strtolower($this->id)),
                ]
            );
        } elseif (!in_array($this->email, [null, '', '0'], true) && filter_var($this->email, FILTER_VALIDATE_EMAIL) && mb_strlen($this->email) <= 255) {
            $results = $db->query(
                "
                    SELECT
                        USER.id, USER.email, USER.password_hash AS passwordHash
                    FROM USER
                        WHERE USER.email = :email
                    ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email)),
                ]
            );
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id,email");
        }

        if (count($results) === 1) {
            $this->id = is_string($results[0]->id ?? null) ? $results[0]->id ?? null : null;
            $this->email = is_string($results[0]->email ?? null) ? $results[0]->email ?? null : null;
            $this->passwordHash = is_string($results[0]->passwordHash ?? null) ? $results[0]->passwordHash ?? null : null;
        } else {
            throw new \Spieldose\Exception\NotFoundException("");
        }
    }

    public function exists(\aportela\DatabaseWrapper\DB $db): bool
    {
        try {
            $this->get($db);
            return (true);
        } catch (\Spieldose\Exception\NotFoundException) {
            return (false);
        }
    }

    public static function isEmailUsed(\aportela\DatabaseWrapper\DB $db, string $email): bool
    {
        $results = null;
        if ($email !== '' && $email !== '0' && filter_var($email, FILTER_VALIDATE_EMAIL) && mb_strlen($email) <= 255) {
            $results = $db->query(
                "
                    SELECT
                        USER.id
                    FROM USER
                        WHERE USER.email = :email
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($email)),
                ]
            );
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id,email");
        }

        return (count($results) === 1);
    }

    public function login(\aportela\DatabaseWrapper\DB $db): bool
    {
        if (!in_array($this->password, [null, '', '0'], true)) {
            $this->get($db);
            if (password_verify((string) $this->password, (string) $this->passwordHash)) {
                if (is_string($this->id) && is_string($this->email)) {
                    \Spieldose\UserSession::init($this->id, $this->email);
                }

                return (true);
            } else {
                throw new \Spieldose\Exception\UnauthorizedException("password");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("password");
        }
    }

    public static function logout(): bool
    {
        \Spieldose\UserSession::clear();
        return (true);
    }
}

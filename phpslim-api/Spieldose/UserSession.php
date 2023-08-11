<?php

declare(strict_types=1);

namespace Spieldose;

class UserSession
{
    public static function set($userId = "", string $email = ""): void
    {
        $_SESSION["userId"] = $userId;
        $_SESSION["email"] = $email;
    }

    public static function clear(): void
    {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            if (PHP_SAPI != 'cli') {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            }
        }
        if (session_status() != PHP_SESSION_NONE) {
            session_destroy();
        }
    }

    public static function isLogged(): bool
    {
        return (isset($_SESSION["userId"]) && !empty($_SESSION["userId"]));
    }

    public static function getUserId(): string
    {
        return ($_SESSION["userId"] ?? "");
    }

    public static function getEmail(): string
    {
        return ($_SESSION["email"] ??  "");
    }
}

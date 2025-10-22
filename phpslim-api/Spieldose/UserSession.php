<?php

declare(strict_types=1);

namespace Spieldose;

class UserSession
{
    public static function set(string $userId = "", string $email = ""): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION["userId"] = $userId;
        $_SESSION["email"] = $email;
    }

    public static function clear(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            $_SESSION = [];
            session_unset();
            if (ini_get("session.use_cookies") && PHP_SAPI != 'cli') {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            }
            session_destroy();
        }
    }

    public static function isLogged(): bool
    {
        return isset($_SESSION["userId"]) && !empty($_SESSION["userId"]);
    }

    public static function getUserId(): string
    {
        return $_SESSION["userId"] ?? '';
    }

    public static function getEmail(): string
    {
        return $_SESSION["email"] ?? '';
    }
}

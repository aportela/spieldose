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
            if (ini_get("session.use_cookies") && PHP_SAPI !== 'cli' && is_string(session_name())) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $params["path"], 'domain' => $params["domain"], 'secure' => $params["secure"], 'httponly' => $params["httponly"]]);
            }

            session_destroy();
        }
    }

    public static function isLogged(): bool
    {
        return array_key_exists("userId", $_SESSION) && is_string($_SESSION["userId"]);
    }

    public static function getUserId(): string
    {
        return array_key_exists("userId", $_SESSION) && is_string($_SESSION["userId"]) ? $_SESSION["userId"] : '';
    }

    public static function getEmail(): string
    {
        return array_key_exists("email", $_SESSION) && is_string($_SESSION["email"]) ? $_SESSION["email"] : '';
    }
}

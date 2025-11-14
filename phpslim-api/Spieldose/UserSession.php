<?php

declare(strict_types=1);

namespace Spieldose;

class UserSession
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params([
                "SameSite" => "Strict",
                "Secure" => true,
                "HttpOnly" => true
            ]);
            session_name('SPIELDOSE');
            session_cache_limiter("nocache");
            session_start();
        }
    }

    public static function set(string $userId = "", string $email = ""): void
    {
        self::start();

        $_SESSION["userId"] = $userId;
        $_SESSION["email"] = $email;
    }

    public static function clear(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_unset();
            $sessionName = session_name();
            if (ini_get("session.use_cookies") && PHP_SAPI !== 'cli' && is_string($sessionName)) {
                $params = session_get_cookie_params();
                setcookie($sessionName, '', [
                    'expires' => time() - 42000,
                    'path' => $params["path"],
                    'domain' => $params["domain"],
                    'secure' => $params["secure"],
                    'httponly' => $params["httponly"]
                ]);
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

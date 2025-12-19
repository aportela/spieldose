<?php

declare(strict_types=1);

namespace Spieldose\Entities;

final class EntityImages
{
    public const string LOCAL_URL_MASK = "api2/thumbnail/local?width=%d&height=%d&quality=90&albumPathId=%s";
    public const string REMOTE_URL_MASK = "api2/thumbnail/remote?width=%d&height=%d&quality=90&url=%s";

    public const int SMALL_WIDTH = 100;
    public const int SMALL_HEIGTH = 100;

    public const int MEDIUM_WIDTH = 400;
    public const int MEDIUM_HEIGTH = 400;

    public const int BIG_WIDTH = 800;
    public const int BIG_HEIGTH = 800;


    public string|null $small;
    public string|null $medium;
    public string|null $big;

    public function __construct(string|null $small = null, string|null $medium = null, string|null $big = null)
    {
        $this->set($small, $medium, $big);
    }

    private function parseAndValidate(string|null $url): string|null
    {
        return ($url);
        // TODO: allow validate URL
        /*
        if (! empty($url)) {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                return ($url);
            } else {
                throw new \InvalidArgumentException("url");
            }
        } else {
            return (null);
        }
        */
    }

    public function set(string|null $small = null, string|null $medium = null, string|null $big = null): void
    {
        $this->small = $this->parseAndValidate($small);
        $this->medium = $this->parseAndValidate($medium);
        $this->big = $this->parseAndValidate($big);
    }

    public function setLocal(string $albumPathId): void
    {
        $this->small = $this->parseAndValidate(sprintf(self::LOCAL_URL_MASK, self::SMALL_WIDTH, self::SMALL_HEIGTH, $albumPathId));
        $this->medium = $this->parseAndValidate(sprintf(self::LOCAL_URL_MASK, self::MEDIUM_WIDTH, self::MEDIUM_HEIGTH, $albumPathId));
        $this->big = $this->parseAndValidate(sprintf(self::LOCAL_URL_MASK, self::BIG_WIDTH, self::BIG_HEIGTH, $albumPathId));
    }

    public function setRemote(string $url): void
    {
        $this->small = $this->parseAndValidate(sprintf(self::REMOTE_URL_MASK, self::SMALL_WIDTH, self::SMALL_HEIGTH, urlencode($url)));
        $this->medium = $this->parseAndValidate(sprintf(self::REMOTE_URL_MASK, self::MEDIUM_WIDTH, self::MEDIUM_HEIGTH, urlencode($url)));
        $this->big = $this->parseAndValidate(sprintf(self::REMOTE_URL_MASK, self::BIG_WIDTH, self::BIG_HEIGTH, urlencode($url)));
    }

    public static function getAlbumLocalImagePath(\aportela\DatabaseWrapper\DB $dbh, string $albumPathId): string|null
    {
        $results = $dbh->query(
            "
                SELECT
                    CONCAT(DIRECTORY.path, :directory_separator, DIRECTORY.cover_filename) AS fullPath
                FROM DIRECTORY
                WHERE
                    DIRECTORY.id = :id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_separator", DIRECTORY_SEPARATOR),
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $albumPathId),
            ]
        );
        if (count($results) === 1) {
            return ($results[0]->fullPath);
        } else {
            return (null);
        }
    }
}

<?php

declare(strict_types=1);

namespace Spieldose\Entities;

final class EntityImages
{
    public const string LOCAL_URL_MASK = "api2/local_thumbnail?width=%d&height=%d&quality=90&pathId=%s";
    public const string REMOTE_URL_MASK = "api2/remote_thumbnail?width=%d&height=%d&quality=90&url=%s";

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

    public function setLocal(string $pathId): void
    {
        $this->small = $this->parseAndValidate(sprintf(self::LOCAL_URL_MASK, self::SMALL_WIDTH, self::SMALL_HEIGTH, $pathId));
        $this->medium = $this->parseAndValidate(sprintf(self::LOCAL_URL_MASK, self::SMALL_WIDTH, self::SMALL_HEIGTH, $pathId));
        $this->big = $this->parseAndValidate(sprintf(self::LOCAL_URL_MASK, self::SMALL_WIDTH, self::SMALL_HEIGTH, $pathId));
    }

    public function setRemote(string $url): void
    {
        $this->small = $this->parseAndValidate(sprintf(self::REMOTE_URL_MASK, self::SMALL_WIDTH, self::SMALL_HEIGTH, urlencode($url)));
        $this->medium = $this->parseAndValidate(sprintf(self::REMOTE_URL_MASK, self::SMALL_WIDTH, self::SMALL_HEIGTH, urlencode($url)));
        $this->big = $this->parseAndValidate(sprintf(self::REMOTE_URL_MASK, self::SMALL_WIDTH, self::SMALL_HEIGTH, urlencode($url)));
    }
}

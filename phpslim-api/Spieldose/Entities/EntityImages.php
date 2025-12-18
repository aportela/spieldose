<?php

declare(strict_types=1);

namespace Spieldose\Entities;

final class EntityImages
{
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
}

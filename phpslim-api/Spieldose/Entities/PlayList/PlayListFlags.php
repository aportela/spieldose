<?php

declare(strict_types=1);

namespace Spieldose\Entities\PlayList;

final class PlayListFlags
{
    private null|int $openedAtTimestamp;
    private null|int $activedAtTimestamp;
    private null|int $publishedAtTimestamp;
    private null|int $sharedAtTimestamp;

    public bool $isMine;
    public bool $isFavorites;
    public bool $isOpened;
    public bool $isActive;
    public bool $isPublished;
    public bool $isShared;

    public function __construct(bool $isMine, bool $isFavorites, null|int $openedAtTimestamp, null|int $activedAtTimestamp, null|int $publishedAtTimestamp, null|int $sharedAtTimestamp)
    {
        $this->setFlags($isMine, $isFavorites, $openedAtTimestamp, $activedAtTimestamp, $publishedAtTimestamp, $sharedAtTimestamp);
    }

    public function setFlags(bool $isMine, bool $isFavorites, null|int $openedAtTimestamp, null|int $activedAtTimestamp, null|int $publishedAtTimestamp, null|int $sharedAtTimestamp): void
    {
        $this->isMine = $isMine;
        $this->isFavorites = $isFavorites;
        $this->openedAtTimestamp = $openedAtTimestamp;
        $this->isOpened = $this->openedAtTimestamp !== null && $this->openedAtTimestamp > 0;
        $this->activedAtTimestamp = $activedAtTimestamp;
        $this->isActive = $this->activedAtTimestamp !== null && $this->activedAtTimestamp > 0;
        $this->publishedAtTimestamp = $publishedAtTimestamp;
        $this->isPublished = $this->publishedAtTimestamp !== null && $this->publishedAtTimestamp > 0;
        $this->sharedAtTimestamp = $sharedAtTimestamp;
        $this->isShared = $this->sharedAtTimestamp !== null && $this->sharedAtTimestamp > 0;
    }

    public function getOpenedAtTimestamp(): null|int
    {
        return ($this->openedAtTimestamp);
    }

    public function getActivedAtTimestamp(): null|int
    {
        return ($this->activedAtTimestamp);
    }

    public function getPublishedAtTimestamp(): null|int
    {
        return ($this->publishedAtTimestamp);
    }

    public function getSharedAtTimestamp(): null|int
    {
        return ($this->sharedAtTimestamp);
    }

    public function open(int $timestamp): void
    {
        $this->openedAtTimestamp = $timestamp;
        $this->isOpened = true;
    }

    public function close(): void
    {
        $this->openedAtTimestamp = null;
        $this->isOpened = false;
    }

    public function setActive(int $timestamp): void
    {
        $this->activedAtTimestamp = $timestamp;
        $this->isActive = true;
    }

    public function unsetActive(): void
    {
        $this->activedAtTimestamp = null;
        $this->isActive = false;
    }
}

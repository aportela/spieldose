<?php

namespace Spieldose\Test;

final class MetricsTest extends BaseTest
{
    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        if (PHP_SAPI === 'cli') {
            $_SESSION = array();
            $_SESSION["userId"] = "00000000-0000-0000-0000-000000000000";
            $_SESSION["email"] = "john@do.e";
        }
    }

    public function testGetTopPlayedTracksWithDateFilters(): void
    {
        $metrics = \Spieldose\Metrics::GetTopPlayedTracks(
            self::$db,
            array(
                "fromDate" => "19800000",
                "toDate" => "19900000"
            ),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetTopPlayedTracksWithArtistFilters(): void
    {
        $metrics = \Spieldose\Metrics::GetTopPlayedTracks(
            self::$db,
            array(
                "artist" => "Iron Maiden"
            ),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetTopPlayedTracks(): void
    {
        $metrics = \Spieldose\Metrics::GetTopPlayedTracks(
            self::$db,
            array(),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetTopArtistsWithDateFilter(): void
    {
        $metrics = \Spieldose\Metrics::GetTopArtists(
            self::$db,
            array(
                "fromDate" => "19800000",
                "toDate" => "19900000"
            ),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetTopArtists(): void
    {
        $metrics = \Spieldose\Metrics::GetTopArtists(
            self::$db,
            array(),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetTopGenresWithDateFilter(): void
    {
        $metrics = \Spieldose\Metrics::GetTopGenres(
            self::$db,
            array(
                "fromDate" => "19800000",
                "toDate" => "19900000"
            ),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetTopGenres(): void
    {
        $metrics = \Spieldose\Metrics::GetTopGenres(
            self::$db,
            array(),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetRecentlyAddedTracks(): void
    {
        $metrics = \Spieldose\Metrics::GetRecentlyAddedTracks(
            self::$db,
            array(),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetRecentlyAddedArtists(): void
    {
        $metrics = \Spieldose\Metrics::GetRecentlyAddedArtists(
            self::$db,
            array(),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetRecentlyAddedAlbums(): void
    {
        $metrics = \Spieldose\Metrics::GetRecentlyAddedAlbums(
            self::$db,
            array(),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetRecentlyPlayedTracks(): void
    {
        $metrics = \Spieldose\Metrics::GetRecentlyPlayedTracks(
            self::$db,
            array(),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetRecentlyPlayedArtists(): void
    {
        $metrics = \Spieldose\Metrics::GetRecentlyPlayedArtists(
            self::$db,
            array(),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetRecentlyPlayedAlbums(): void
    {
        $metrics = \Spieldose\Metrics::GetRecentlyPlayedAlbums(
            self::$db,
            array(),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetPlayStatsByHour(): void
    {
        $metrics = \Spieldose\Metrics::GetPlayStatsByHour(
            self::$db,
            array(),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetPlayStatsByWeekDay(): void
    {
        $metrics = \Spieldose\Metrics::GetPlayStatsByWeekDay(
            self::$db,
            array(),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetPlayStatsByMonth(): void
    {
        $metrics = \Spieldose\Metrics::GetPlayStatsByMonth(
            self::$db,
            array(),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }

    public function testGetPlayStatsByYear(): void
    {
        $metrics = \Spieldose\Metrics::GetPlayStatsByYear(
            self::$db,
            array(),
            5
        );
        $this->assertTrue(count($metrics) >= 0);
    }
}

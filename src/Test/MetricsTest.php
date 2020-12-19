<?php

    declare(strict_types=1);

    namespace Spieldose\Test;

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    final class MetricsTest extends \PHPUnit\Framework\TestCase
    {
        static private $app = null;
        static private $container = null;
        static private $dbh = null;

        /**
         * Called once just like normal constructor
         */
        public static function setUpBeforeClass (): void {
            self::$app = (new \Spieldose\App())->get();
            self::$container = self::$app->getContainer();
            self::$dbh = new \Spieldose\Database\DB(self::$container);
        }

        /**
         * Initialize the test case
         * Called for every defined test
         */
        public function setUp(): void {
            self::$dbh->beginTransaction();
        }

        /**
         * Clean up the test case, called for every defined test
         */
        public function tearDown(): void {
            self::$dbh->rollBack();
        }

        /**
         * Clean up the whole test class
         */
        public static function tearDownAfterClass(): void {
            self::$dbh = null;
            self::$container = null;
            self::$app = null;
        }

        public function testGetTopPlayedTracksWithDateFilters(): void {
            $metrics = \Spieldose\Metrics::GetTopPlayedTracks(
                self::$dbh,
                array(
                    "fromDate" => "19800000",
                    "toDate" => "19900000"
                ),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetTopPlayedTracksWithArtistFilters(): void {
            $metrics = \Spieldose\Metrics::GetTopPlayedTracks(
                self::$dbh,
                array(
                    "artist" => "Iron Maiden"
                ),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetTopPlayedTracks(): void {
            $metrics = \Spieldose\Metrics::GetTopPlayedTracks(
                self::$dbh,
                array(),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetTopArtistsWithDateFilter(): void {
            $metrics = \Spieldose\Metrics::GetTopArtists(
                self::$dbh,
                array(
                    "fromDate" => "19800000",
                    "toDate" => "19900000"
                ),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetTopArtists(): void {
            $metrics = \Spieldose\Metrics::GetTopArtists(
                self::$dbh,
                array(),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetTopGenresWithDateFilter(): void {
            $metrics = \Spieldose\Metrics::GetTopGenres(
                self::$dbh,
                array(
                    "fromDate" => "19800000",
                    "toDate" => "19900000"
                ),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetTopGenres(): void {
            $metrics = \Spieldose\Metrics::GetTopGenres(
                self::$dbh,
                array(),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetRecentlyAddedTracks(): void {
            $metrics = \Spieldose\Metrics::GetRecentlyAddedTracks(
                self::$dbh,
                array(),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetRecentlyAddedArtists(): void {
            $metrics = \Spieldose\Metrics::GetRecentlyAddedArtists(
                self::$dbh,
                array(),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetRecentlyAddedAlbums(): void {
            $metrics = \Spieldose\Metrics::GetRecentlyAddedAlbums(
                self::$dbh,
                array(),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetRecentlyPlayedTracks(): void {
            $metrics = \Spieldose\Metrics::GetRecentlyPlayedTracks(
                self::$dbh,
                array(),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetRecentlyPlayedArtists(): void {
            $metrics = \Spieldose\Metrics::GetRecentlyPlayedArtists(
                self::$dbh,
                array(),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetRecentlyPlayedAlbums(): void {
            $metrics = \Spieldose\Metrics::GetRecentlyPlayedAlbums(
                self::$dbh,
                array(),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetPlayStatsByHour(): void {
            $metrics = \Spieldose\Metrics::GetPlayStatsByHour(
                self::$dbh,
                array(),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetPlayStatsByWeekDay(): void {
            $metrics = \Spieldose\Metrics::GetPlayStatsByWeekDay(
                self::$dbh,
                array(),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetPlayStatsByMonth(): void {
            $metrics = \Spieldose\Metrics::GetPlayStatsByMonth(
                self::$dbh,
                array(),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

        public function testGetPlayStatsByYear(): void {
            $metrics = \Spieldose\Metrics::GetPlayStatsByYear(
                self::$dbh,
                array(),
                5
            );
            $this->assertTrue(count($metrics) >= 0);
        }

    }
?>
<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

echo "Spieldose scraper" . PHP_EOL;

$logger = $container->get(\Spieldose\Logger\ScraperLogger::class);

$logger->info("Scrap started");

$settings = $container->get('settings');

$missingExtensions = array_diff($settings["phpRequiredExtensions"], get_loaded_extensions());
if (count($missingExtensions) > 0) {
    $missingExtensionsStr = implode(", ", $missingExtensions);
    echo "Error: missing php extension/s: " . $missingExtensionsStr . PHP_EOL;
    $logger->critical("Error: missing php extension/s: ", [$missingExtensionsStr]);
} else {
    try {
        $db = $container->get(\aportela\DatabaseWrapper\DB::class);
        if ($db->getCurrentSchemaVersion() < $db->getUpgradeSchemaVersion()) {
            echo "New database version available, an upgrade is required before continue." . PHP_EOL;
            exit;
        }
        $cmdLine = new \Spieldose\CmdLine("", array("all", "artists", "albums", "cleanup"));
        if ($cmdLine->hasParam("all") || $cmdLine->hasParam("artists") || $cmdLine->hasParam("albums") || $cmdLine->hasParam("cleanup")) {
            $scrapArtists = $cmdLine->hasParam("artists") || $cmdLine->hasParam("all");
            $scrapAlbums = $cmdLine->hasParam("albums") || $cmdLine->hasParam("all");
            if ($scrapArtists || $scrapAlbums) {
                $scraper = new \Spieldose\Scraper($db, $logger);
                if ($scrapArtists) {
                    echo "Artist scraping...." . PHP_EOL;
                    $logger->info("Scraping artists");
                    $artistNames = $scraper->getArtistNamesWithoutMusicBrainzId();
                    $totalArtistNames = count($artistNames);
                    if ($totalArtistNames > 0) {
                        echo sprintf("Processing %d artist name/s without MusicBrainzId%s", $totalArtistNames, PHP_EOL);
                        for ($i = 0; $i < $totalArtistNames; $i++) {
                            $mbId = $scraper->searchArtistMusicBrainzIdByName($artistNames[$i]);
                            if (!empty($mbId)) {
                                $scraper->saveArtistMusicBrainzId($mbId, $artistNames[$i]);
                            }
                            sleep(1); // wait 1 second between queries for prevent too much remote api requests in small amount of time and get banned
                            \Spieldose\Utils::showProgressBar($i + 1, $totalArtistNames, 20);
                        }
                    } else {
                        echo sprintf("No artist names without MusicBrainzId found%s", PHP_EOL);
                    }
                    $artistMBIds = $scraper->getArtistMusicBrainzIdsWithoutCachedMetadata();
                    $totalArtistMBIds = count($artistMBIds);
                    if ($totalArtistMBIds > 0) {
                        echo sprintf("Processing %d artist without MusicBrainz cached metadata%s", $totalArtistMBIds, PHP_EOL);
                        for ($i = 0; $i < $totalArtistMBIds; $i++) {
                            $mbArtist = $scraper->getArtistMusicBrainzMetadata($artistMBIds[$i]);
                            if (!empty($mbArtist->mbId) && !empty($mbArtist->name)) {
                                $scraper->saveArtistMusicBrainzCachedMetadata($mbArtist);
                            }
                            sleep(1); // wait 1 second between queries for prevent too much remote api requests in small amount of time and get banned
                            \Spieldose\Utils::showProgressBar($i + 1, $totalArtistMBIds, 20);
                        }
                    } else {
                        echo sprintf("All Artist MusicBrainz metadata is cached%s", PHP_EOL);
                    }
                }
                if ($scrapAlbums) {
                    echo "Album scraping...." . PHP_EOL;
                    $logger->info("Scraping albums");
                    $albums = $scraper->getAlbumsWithoutMusicBrainzId();
                    $totalAlbums = count($albums);
                    if (false && $totalAlbums > 0) {
                        echo sprintf("Processing %d album/s without MusicBrainzId%s", $totalAlbums, PHP_EOL);
                        for ($i = 0; $i < $totalAlbums; $i++) {
                            $mbId = $scraper->searchAlbumMusicBrainzId($albums[$i]->album, $albums[$i]->artist, $albums[$i]->year);
                            if (!empty($mbId)) {
                                $scraper->saveAlbumMusicBrainzId($mbId, $albums[$i]->album, $albums[$i]->artist, $albums[$i]->year);
                            }
                            sleep(1); // wait 1 second between queries for prevent too much remote api requests in small amount of time and get banned
                            \Spieldose\Utils::showProgressBar($i + 1, $totalAlbums, 20);
                        }
                    } else {
                        echo sprintf("No albums without MusicBrainzId found%s", PHP_EOL);
                    }

                    $albumMBIds = $scraper->getAlbumMusicBrainzIdsWithoutCachedMetadata();
                    $totalAlbumMBIds = count($albumMBIds);
                    if ($totalAlbumMBIds > 0) {
                        echo sprintf("Processing %d albums without MusicBrainz cached metadata%s", $totalAlbumMBIds, PHP_EOL);
                        for ($i = 0; $i < $totalAlbumMBIds; $i++) {
                            $mbAlbum = $scraper->getAlbumMusicBrainzMetadata($albumMBIds[$i]);
                            if (!empty($mbAlbum->mbId) && !empty($mbAlbum->title)) {
                                $scraper->saveAlbumMusicBrainzCachedMetadata($mbAlbum);
                            }
                            sleep(1); // wait 1 second between queries for prevent too much remote api requests in small amount of time and get banned
                            \Spieldose\Utils::showProgressBar($i + 1, $totalAlbumMBIds, 20);
                        }
                    } else {
                        echo sprintf("All Album MusicBrainz metadata is cached%s", PHP_EOL);
                    }
                }
            }
            if ($cmdLine->hasParam(("cleanup"))) {
                echo "Cleaning database" . PHP_EOL;
                $logger->debug("Cleaning database");
                $scraper->cleanUp();
            }
        } else {
            echo "No required params found: --all or --artists or --albums or --cleanup" . PHP_EOL;
        }
    } catch (\Exception $e) {
        echo "Uncaught exception: " . $e->getMessage() . PHP_EOL;
        $logger->critical("Uncaught exception: " . $e->getMessage());
    }
}

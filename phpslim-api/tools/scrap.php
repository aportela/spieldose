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
                    $pendingArtists = $scraper->getPendingArtists();
                    $totalPendingArtists = count($pendingArtists);
                    if ($totalPendingArtists > 0) {
                        echo sprintf("Processing %d artist/s%s", $totalPendingArtists, PHP_EOL);
                        $failed = array();
                        for ($i = 0; $i < $totalPendingArtists; $i++) {
                            try {
                                $logger->info("Searching on MusicBrainz artist: " . $pendingArtists[$i]);
                                $scraper->mbArtistScrap($pendingArtists[$i]);
                            } catch (\Throwable $e) {
                                $logger->error("Error: " . $e->getMessage(), array('file' => __FILE__, 'line' => __LINE__));
                                $failed[] = $pendingArtists[$i];
                            }
                            sleep(1); // wait 1 second between queries for prevent too much remote api requests in small amount of time and get banned
                            \Spieldose\Utils::showProgressBar($i + 1, $totalPendingArtists, 20);
                        }
                        $totalFailed = count($failed);
                        if ($totalFailed > 0) {
                            echo sprintf("Failed to scrap %d artists:%s", $totalFailed, PHP_EOL);
                            print_r($failed);
                        }
                    } else {
                        echo "No pending artists found to scrap" . PHP_EOL;
                    }
                    $mbIds = $scraper->getPendingArtistMBIds();
                    $totalMBIds = count($mbIds);
                    if ($totalMBIds > 0) {
                        echo sprintf("Scrapping %d pending artist MusicBrainz ids%s", $totalMBIds, PHP_EOL);
                        $failed = array();
                        for ($i = 0; $i < $totalMBIds; $i++) {
                            try {
                                $logger->info("Getting MusicBrainz artist id: " . $mbIds[$i]);
                                $scraper->mbArtistMBIdscrap($mbIds[$i]);
                            } catch (\Throwable $e) {
                                $logger->error("Error: " . $e->getMessage(), array('file' => __FILE__, 'line' => __LINE__));
                                $failed[] = $mbIds[$i];
                            }
                            \Spieldose\Utils::showProgressBar($i + 1, $totalMBIds, 20);
                            sleep(1);
                        }
                        $totalFailed = count($failed);
                        if ($totalFailed > 0) {
                            echo "Failed MusicBrainz Artist ids: " . implode(", ", $failed) . PHP_EOL;
                        }
                    } else {
                        echo "No pending MusicBrainz Artist ids found to scrap" . PHP_EOL;
                    }
                }
                if ($scrapAlbums) {
                    echo "Album scraping...." . PHP_EOL;
                    $logger->info("Scraping albums");
                    $pendingAlbums = $scraper->getPendingAlbums();
                    $totalPendingAlbums = count($pendingAlbums);
                    if ($totalPendingAlbums > 0) {
                        echo sprintf("Processing %d albums%s", $totalPendingAlbums, PHP_EOL);
                        $failed = array();
                        for ($i = 0; $i < $totalPendingAlbums; $i++) {
                            try {
                                //$c["scrapLogger"]->debug("Searching on MusicBrainz album: " . $pendingAlbums[$i]->album . " of artist:" . $pendingAlbums[$i]->artist);
                                $scraper->mbAlbumScrap($pendingAlbums[$i]->album, $pendingAlbums[$i]->artist, $pendingAlbums[$i]->year);
                            } catch (\Throwable $e) {
                                //$c["scrapLogger"]->error("Error: " . $e->getMessage());
                                $failed[] = $pendingAlbums[$i];
                            }
                            \Spieldose\Utils::showProgressBar($i + 1, $totalPendingAlbums, 20);
                            sleep(1);
                        }
                        $totalFailed = count($failed);
                        if ($totalFailed > 0) {
                            echo sprintf("Failed to scrap %d albums:%s", $totalFailed, PHP_EOL);
                            print_r($failed);
                        }
                    }
                    $mbIds = $scraper->getPendingAlbumMBIds();
                    $totalMBIds = count($mbIds);
                    if ($totalMBIds > 0) {
                        echo sprintf("Scrapping %d pending album MusicBrainz ids%s", $totalMBIds, PHP_EOL);
                        $failed = array();
                        for ($i = 0; $i < $totalMBIds; $i++) {
                            try {
                                //$c["scrapLogger"]->debug("Getting MusicBrainz album id: " . $mbIds[$i]);
                                $scraper->mbAlbumMBIdscrap($mbIds[$i]);
                            } catch (\Throwable $e) {
                                //$c["scrapLogger"]->error("Error: " . $e->getMessage());
                                $failed[] = $mbIds[$i];
                                die($e->getMessage());
                            }
                            \Spieldose\Utils::showProgressBar($i + 1, $totalMBIds, 20);
                            sleep(1);
                        }
                        $totalFailed = count($failed);
                        if ($totalFailed > 0) {
                            echo "Failed MusicBrainz Album ids: " . implode(", ", $failed) . PHP_EOL;
                        }
                    } else {
                        echo "No pending MusicBrainz Album ids found to scrap" . PHP_EOL;
                    }
                }
                //$c["scrapLogger"]->info("Scraper finished");
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

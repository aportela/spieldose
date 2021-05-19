<?php

    declare(strict_types=1);

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    echo "Spieldose music scraper" . PHP_EOL;

    $app = (new \Spieldose\App())->get();

    $missingExtensions = array_diff($app->getContainer()["settings"]["phpRequiredExtensions"], get_loaded_extensions());
    if (count($missingExtensions) > 0) {
        echo "Error: missing php extension/s: " . implode(", ", $missingExtensions) . PHP_EOL;
        exit;
    }

    $cmdLine = new \Spieldose\CmdLine("", array("all", "artists", "albums"));
    $scrapArtists = $cmdLine->hasParam("artists") || $cmdLine->hasParam("all");
    $scrapAlbums = $cmdLine->hasParam("albums") || $cmdLine->hasParam("all");
    if ($scrapArtists || $scrapAlbums) {
        $c = $app->getContainer();
        $c["scrapLogger"]->info("Scraper started");
        $dbh = new \Spieldose\Database\DB($c);
        if ((new \Spieldose\Database\Version($dbh, $c->get("settings")['database']['type']))->hasUpgradeAvailable()) {
            $c["scrapLogger"]->warning("Process stopped: upgrade database before continue");
            echo "New database version available, an upgrade is required before continue." . PHP_EOL;
            exit;
        }
        $scraper = new \Spieldose\Scrapper($dbh);
        if ($scrapArtists) {
            echo "Artist scraping...." . PHP_EOL;
            $c["scrapLogger"]->info("Scraping artists");
            $pendingArtistsWithoutMBId = $scraper->getPendingArtistsWithoutMBId();
            $total = count($pendingArtistsWithoutMBId);
            $total = 0;
            if ($total > 0) {
                echo sprintf("Processing %d artist/s without MusicBrainz Id%s", $total, PHP_EOL);
                $failed = array();
                for ($i = 0; $i < $total; $i++) {
                    try {
                        $c["scrapLogger"]->debug("Searching MusicBrainz Id for artist: " . $pendingArtistsWithoutMBId[$i]);
                        $mbId = $scraper->scrapArtistMBIdFromName($pendingArtistsWithoutMBId[$i]);
                        if (! empty($mbId)) {
                            $scraper->setArtistMBIdFromName($mbId, $pendingArtistsWithoutMBId[$i]);
                        } else {
                            throw new \Exception("MUSICBRAINZ API - ARTIST NOT FOUND: " . $name);
                        }
                    } catch (\Throwable $e) {
                        $c["scrapLogger"]->error("Error: " . $e->getMessage(), array('file' => __FILE__, 'line' => __LINE__));
                        $failed[] = $pendingArtistsWithoutMBId[$i];
                    }
                    sleep(1);
                    \Spieldose\Utils::showProgressBar($i + 1, $total, 20);
                }
            } else {
                echo "No pending Artists without music brainz id" . PHP_EOL;
            }
            $mbIds = $scraper->getArtistMBIdsWithoutCache();
            $total = count($mbIds);
            if ($total > 0) {
                echo sprintf("Scrapping %d pending artist MusicBrainz ids%s", $total, PHP_EOL);
                $failed = array();
                for ($i = 0; $i < $total; $i++) {
                    try {
                        $c["scrapLogger"]->debug("Getting MusicBrainz artist id: " . $mbIds[$i]);
                        $scraper->setArtistMBCacheFromMBId($mbIds[$i]);
                    } catch (\Throwable $e) {
                        // TODO: music brainz artist id not found, delete from LOCAL database
                        $c["scrapLogger"]->error("Error: " . $e->getMessage());
                        $failed[] = $mbIds[$i];
                    }
                    \Spieldose\Utils::showProgressBar($i + 1, $total, 20);
                    sleep(1);
                }
                $totalFailed = count($failed);
                if ($totalFailed > 0) {
                    echo "Failed MusicBrainz Artist ids: " . implode(", ",$failed) . PHP_EOL;
                }
            } else {
                echo "No pending MusicBrainz Artist ids found to scrap" . PHP_EOL;
            }
        }
        if ($scrapAlbums) {
            echo "Album scraping...." . PHP_EOL;
            $c["scrapLogger"]->info("Scraping albums");
            $pendingAlbums = $scraper->getPendingAlbums();
            $totalPendingAlbums = count($pendingAlbums);
            if ($totalPendingAlbums > 0) {
                echo sprintf("Processing %d albums%s", $totalPendingAlbums, PHP_EOL);
                $failed = array();
                for ($i = 0; $i < $totalPendingAlbums; $i++) {
                    try {
                        $c["scrapLogger"]->debug("Searching on MusicBrainz album: " . $pendingAlbums[$i]->album . " of artist:" . $pendingAlbums[$i]->artist);
                        $scraper->mbAlbumScrap($pendingAlbums[$i]->album, $pendingAlbums[$i]->artist);
                    } catch (\Throwable $e) {
                        $c["scrapLogger"]->error("Error: " . $e->getMessage());
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
            $total = count($mbIds);
            if ($total > 0) {
                echo sprintf("Scrapping %d pending album MusicBrainz ids%s", $total, PHP_EOL);
                $failed = array();
                for ($i = 0; $i < $total; $i++) {
                    try {
                        $c["scrapLogger"]->debug("Getting MusicBrainz album id: " . $mbIds[$i]);
                        $scraper->mbAlbumMBIdscrap($mbIds[$i]);
                    } catch (\Throwable $e) {
                        $c["scrapLogger"]->error("Error: " . $e->getMessage());
                        $failed[] = $mbIds[$i];
                    }
                    \Spieldose\Utils::showProgressBar($i + 1, $total, 20);
                    sleep(1);
                }
                $totalFailed = count($failed);
                if ($totalFailed > 0) {
                    echo "Failed MusicBrainz Album ids: " . implode(", ",$failed) . PHP_EOL;
                }
            } else {
                echo "No pending MusicBrainz Album ids found to scrap" . PHP_EOL;
            }
        }
        $c["scrapLogger"]->info("Scraper finished");
    } else {
        echo "No required params found: --artists or --albums or --all" . PHP_EOL;
    }

?>
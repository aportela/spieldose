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
        $dbh = new \Spieldose\Database\DB();
        if ((new \Spieldose\Database\Version($dbh))->hasUpgradeAvailable()) {
            $c["scrapLogger"]->warning("Process stopped: upgrade database before continue");
            echo "New database version available, an upgrade is required before continue." . PHP_EOL;
            exit;
        }
        $scraper = new \Spieldose\Scrapper($dbh);
        if ($scrapArtists) {
            echo "Artist scraping...." . PHP_EOL;
            $c["scrapLogger"]->info("Scraping artists");
            $pendingArtists = $scraper->getPendingArtists();
            $totalPendingArtists = count($pendingArtists);
            if ($totalPendingArtists > 0) {
                echo sprintf("Processing %d artist/s%s", $totalPendingArtists, PHP_EOL);
                $failed = array();
                for ($i = 0; $i < $totalPendingArtists; $i++) {
                    try {
                        $c["scrapLogger"]->debug("Searching on MusicBrainz artist: " . $pendingArtists[$i]);
                        $scraper->mbArtistScrap($pendingArtists[$i]);
                    } catch (\Throwable $e) {
                        $c["scrapLogger"]->error("Error: " . $e->getMessage(), array('file' => __FILE__, 'line' => __LINE__));
                        $failed[] = $pendingArtists[$i];
                    }
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
                        $c["scrapLogger"]->debug("Getting MusicBrainz artist id: " . $mbIds[$i]);
                        $scraper->mbArtistMBIdscrap($mbIds[$i]);
                    } catch (\Throwable $e) {
                        $c["scrapLogger"]->error("Error: " . $e->getMessage());
                        $failed[] = $mbIds[$i];
                    }
                    \Spieldose\Utils::showProgressBar($i + 1, $totalMBIds, 20);
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
                        $c["scrapLogger"]->debug("Getting MusicBrainz album id: " . $mbIds[$i]);
                        $scraper->mbAlbumMBIdscrap($mbIds[$i]);
                    } catch (\Throwable $e) {
                        $c["scrapLogger"]->error("Error: " . $e->getMessage());
                        $failed[] = $mbIds[$i];
                    }
                    \Spieldose\Utils::showProgressBar($i + 1, $totalMBIds, 20);
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
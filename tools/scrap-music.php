<?php

    declare(strict_types=1);

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    echo "Spieldose music scraper" . PHP_EOL;

    $settings = require dirname(__DIR__) . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "AppSettings.php";

    $app = (new \Spieldose\App($settings))->get();

    $cmdLine = new \Spieldose\CmdLine("", array("all", "artists", "albums"));
    $scrapArtists = $cmdLine->hasParam("artists") || $cmdLine->hasParam("all");
    $scrapAlbums = $cmdLine->hasParam("albums") || $cmdLine->hasParam("all");
    if ($scrapArtists || $scrapAlbums) {
        $scraper = new \Spieldose\Scrapper(new \Spieldose\Database\DB());
        if ($scrapArtists) {
            echo "Artist scraping...." . PHP_EOL;
            $pendingArtists = $scraper->getPendingArtists();
            $totalPendingArtists = count($pendingArtists);
            if ($totalPendingArtists > 0) {
                echo sprintf("Processing %d artist/s%s", $totalPendingArtists, PHP_EOL);
                $failed = array();
                for ($i = 0; $i < $totalPendingArtists; $i++) {
                    try {
                        $scraper->mbArtistScrap($pendingArtists[$i]);
                    } catch (\Throwable $e) {
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
                        $scraper->mbArtistMBIdscrap($mbIds[$i]);
                    } catch (\Throwable $e) {
                        $failed[] = $mbIds[$i];
                    }
                    \Spieldose\Utils::showProgressBar($i + 1, $totalMBIds, 20);
                }
                $totalFailed = count($failed);
                if ($totalFailed > 0) {
                    echo sprintf("Failed to scrap %d artist MusicBrainz ids:%s", $totalFailed, PHP_EOL);
                    print_r($failed);
                }
            } else {
                echo "No pending artist MusicBrainz ids found to scrap" . PHP_EOL;
            }
        }
        if ($scrapAlbums) {
            echo "Album scraping...." . PHP_EOL;
            $pendingAlbums = $scraper->getPendingAlbums();
            $totalPendingAlbums = count($pendingAlbums);
            if ($totalPendingAlbums > 0) {
                echo sprintf("Processing %d albums%s", $totalPendingAlbums, PHP_EOL);
                $failed = array();
                for ($i = 0; $i < $totalPendingAlbums; $i++) {
                    try {
                        $scraper->mbAlbumScrap($pendingAlbums[$i]->album, $pendingAlbums[$i]->artist);
                    } catch (\Throwable $e) {
                        print_r($e);
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
                        $scraper->mbAlbumMBIdscrap($mbIds[$i]);
                    } catch (\Throwable $e) {
                        $failed[] = $mbIds[$i];
                    }
                    \Spieldose\Utils::showProgressBar($i + 1, $totalMBIds, 20);
                }
                $totalFailed = count($failed);
                if ($totalFailed > 0) {
                    echo sprintf("Failed to scrap %d album mbids:%s", $totalFailed, PHP_EOL);
                    print_r($failed);
                }
            } else {
                echo "No pending album MusicBrainz ids found to scrap" . PHP_EOL;
            }
        }
    } else {
        echo "No required params found: --artists or --albums or --all" . PHP_EOL;
    }

?>
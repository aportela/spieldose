<?php

    declare(strict_types=1);

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    echo "Spieldose music scraper" . PHP_EOL;

    $settings = require dirname(__DIR__) . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "AppSettings.php";

    $app = (new \Spieldose\App($settings))->get();

    $container = $app->getContainer();

    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['scrapLogger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['path'], 0, $settings['level']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $logger->pushHandler($handler);
        return ($logger);
    };

    $cmdLine = new \Spieldose\CmdLine("", array("all", "artists", "albums"));
    $scrapArtists = $cmdLine->hasParam("artists") || $cmdLine->hasParam("all");
    $scrapAlbums = $cmdLine->hasParam("albums") || $cmdLine->hasParam("all");
    if ($scrapArtists || $scrapAlbums) {
        $scraper = new \Spieldose\Scrapper(new \Spieldose\Database\DB());
        $c = $app->getContainer();
        $c["logger"]->info("Scraper started");
        if ($scrapArtists) {
            echo "Artist scraping...." . PHP_EOL;
            $c["logger"]->info("Scraping artists");
            $pendingArtists = $scraper->getPendingArtists();
            $totalPendingArtists = count($pendingArtists);
            if ($totalPendingArtists > 0) {
                echo sprintf("Processing %d artist/s%s", $totalPendingArtists, PHP_EOL);
                $failed = array();
                for ($i = 0; $i < $totalPendingArtists; $i++) {
                    try {
                        $c["logger"]->debug("Searching on MusicBrainz artist: " . $pendingArtists[$i]);
                        $scraper->mbArtistScrap($pendingArtists[$i]);
                    } catch (\Throwable $e) {
                        $c["logger"]->error("Error: " . $e->getMessage(), array('file' => __FILE__, 'line' => __LINE__));
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
                        $c["logger"]->debug("Getting MusicBrainz artist id: " . $mbIds[$i]);
                        $scraper->mbArtistMBIdscrap($mbIds[$i]);
                    } catch (\Throwable $e) {
                        $c["logger"]->error("Error: " . $e->getMessage());
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
            $c["logger"]->info("Scraping albums");
            $pendingAlbums = $scraper->getPendingAlbums();
            $totalPendingAlbums = count($pendingAlbums);
            if ($totalPendingAlbums > 0) {
                echo sprintf("Processing %d albums%s", $totalPendingAlbums, PHP_EOL);
                $failed = array();
                for ($i = 0; $i < $totalPendingAlbums; $i++) {
                    try {
                        $c["logger"]->debug("Searching on MusicBrainz album: " . $pendingAlbums[$i]->album . " of artist:" . $pendingAlbums[$i]->artist);
                        $scraper->mbAlbumScrap($pendingAlbums[$i]->album, $pendingAlbums[$i]->artist);
                    } catch (\Throwable $e) {
                        $c["logger"]->error("Error: " . $e->getMessage());
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
                        $c["logger"]->debug("Getting MusicBrainz album id: " . $mbIds[$i]);
                        $scraper->mbAlbumMBIdscrap($mbIds[$i]);
                    } catch (\Throwable $e) {
                        $c["logger"]->error("Error: " . $e->getMessage());
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
        $c["logger"]->info("Scraper finished");
    } else {
        echo "No required params found: --artists or --albums or --all" . PHP_EOL;
    }

?>
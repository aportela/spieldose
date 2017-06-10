<?php

    namespace Spieldose;

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR. "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    echo "Spieldose scrap utility " . PHP_EOL;

    $cmdLine = new \Spieldose\CmdLine("", array("artists", "albums"));
    if ($cmdLine->hasParam("artists")) {
        $dbh = new \Spieldose\Database();
        $scrapper = new \Spieldose\Scrapper();
        $artists = $scrapper->getPendingArtists($dbh);
        $totalArtists = count($artists);
        $failed = array();
        echo sprintf("Processing %d artists%s", $totalArtists, PHP_EOL);
        for ($i = 0; $i < $totalArtists; $i++) {
            try {
                $scrapper->mbArtistScrap($dbh, $artists[$i]);
            } catch (\Throwable $e) {
                $failed[] = $artists[$i];
            }
            \Spieldose\Utils::showProgressBar($i + 1, $totalArtists, 20);
        }
        $totalFailed = count($failed);
        if ($totalFailed > 0) {
            echo sprintf("Failed to scrap %d artists:%s", $totalFailed, PHP_EOL);
            print_r($failed);
        }
    }
    if ($cmdLine->hasParam("albums")) {
        $dbh = new \Spieldose\Database();
        $scrapper = new \Spieldose\Scrapper();
        $albums = $scrapper->getPendingAlbums($dbh);
        $totalAlbums = count($albums);
        $failed = array();
        echo sprintf("Processing %d albums%s", $totalAlbums, PHP_EOL);
        for ($i = 0; $i < $totalAlbums; $i++) {
            try {
                $scrapper->mbAlbumScrap($dbh, $albums[$i]->album, $albums[$i]->artist);
            } catch (\Throwable $e) {
                print_r($e);
                $failed[] = $albums[$i];
            }
            \Spieldose\Utils::showProgressBar($i + 1, $totalAlbums, 20);
        }
        $totalFailed = count($failed);
        if ($totalFailed > 0) {
            echo sprintf("Failed to scrap %d artists:%s", $totalFailed, PHP_EOL);
            print_r($failed);
        }
    }

?>
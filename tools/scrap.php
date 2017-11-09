<?php

    declare(strict_types=1);

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    echo "Spieldose scrap utility " . PHP_EOL;

    $cmdLine = new \Spieldose\CmdLine("", array("artists", "albums"));
    if ($cmdLine->hasParam("artists")) {
        $dbh = new \Spieldose\Database\DB();
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
        $mbIds = $scrapper->getPendingArtistMBIds($dbh);
        $totalMBIds = count($mbIds);
        $failed = array();
        echo sprintf("Scrapping %d pending artist MusicBrainz ids%s", $totalMBIds, PHP_EOL);
        for ($i = 0; $i < $totalMBIds; $i++) {
            try {
                $scrapper->mbArtistMBIdscrap($dbh, $mbIds[$i]);
            } catch (\Throwable $e) {
                $failed[] = $mbIds[$i];
            }
            \Spieldose\Utils::showProgressBar($i + 1, $totalMBIds, 20);
        }
        $totalFailed = count($failed);
        if ($totalFailed > 0) {
            echo sprintf("Failed to scrap %d artist mbids:%s", $totalFailed, PHP_EOL);
            print_r($failed);
        }
    }
    if ($cmdLine->hasParam("albums")) {
        $dbh = new \Spieldose\Database\DB();
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
            echo sprintf("Failed to scrap %d albums:%s", $totalFailed, PHP_EOL);
            print_r($failed);
        }
        $mbIds = $scrapper->getPendingAlbumMBIds($dbh);
        $totalMBIds = count($mbIds);
        $failed = array();
        echo sprintf("Scrapping %d pending album MusicBrainz ids%s", $totalMBIds, PHP_EOL);
        for ($i = 0; $i < $totalMBIds; $i++) {
            try {
                $scrapper->mbAlbumMBIdscrap($dbh, $mbIds[$i]);
            } catch (\Throwable $e) {
                $failed[] = $mbIds[$i];
            }
            \Spieldose\Utils::showProgressBar($i + 1, $totalMBIds, 20);
        }
        $totalFailed = count($failed);
        if ($totalFailed > 0) {
            echo sprintf("Failed to scrap %d artist mbids:%s", $totalFailed, PHP_EOL);
            print_r($failed);
        }
    }

?>
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

const SECONDS_BETWEEN_API_SCRAPS = 1;

$settings = $container->get('settings');

$missingExtensions = array_diff($settings["phpRequiredExtensions"], get_loaded_extensions());
if (count($missingExtensions) > 0) {
    $missingExtensionsStr = implode(", ", $missingExtensions);
    echo "Error: missing php extension/s: " . $missingExtensionsStr . PHP_EOL;
    $logger->critical("Error: missing php extension/s: ", [$missingExtensionsStr]);
} else {
    try {
        $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
        if ($dbh->getCurrentSchemaVersion() < $dbh->getUpgradeSchemaVersion()) {
            echo "New database version available, an upgrade is required before continue." . PHP_EOL;
            exit;
        }

        echo "Checking artist names without musicbrainz id set...";
        $artistNamesWithoutMusicBrainzId = \Spieldose\Scraper\Artist\Scraper::getArtistNamesWithoutMusicBrainzId($dbh, true);
        $total = count($artistNamesWithoutMusicBrainzId);
        if ($total > 0) {
            echo sprintf(" %d found%s", $total, PHP_EOL);
            \Spieldose\Utils::showProgressBar(1, $total, 20, "Name: " . $artistNamesWithoutMusicBrainzId[0]);
            for ($i = 0; $i < $total; $i++) {
                if ($i != 0) {
                    sleep(SECONDS_BETWEEN_API_SCRAPS); // wait between queries for prevent too much remote api requests in small amount of time and get banned
                }
                \Spieldose\Scraper\Artist\Scraper::scrapMusicBrainz($logger, $dbh, null, $artistNamesWithoutMusicBrainzId[$i]);
                \Spieldose\Utils::showProgressBar($i + 1, $total, 20, "Name: " . $artistNamesWithoutMusicBrainzId[$i]);
            }
        } else {
            echo " none found" . PHP_EOL;
        }

        echo "Checking artists without musicbrainz cached data... ";
        $artistMBIdsWithoutCache = \Spieldose\Scraper\Artist\Scraper::getMusicBrainzArtistMbIdsWithoutCache($dbh, true);
        $total = count($artistMBIdsWithoutCache);
        if ($total > 0) {
            echo sprintf(" %d found%s", $total, PHP_EOL);
            \Spieldose\Utils::showProgressBar(1, $total, 20, "MBId: " . $artistMBIdsWithoutCache[0]);
            for ($i = 0; $i < $total; $i++) {
                if ($i != 0) {
                    sleep(SECONDS_BETWEEN_API_SCRAPS); // wait between queries for prevent too much remote api requests in small amount of time and get banned
                }
                \Spieldose\Scraper\Artist\Scraper::scrapMusicBrainz($logger, $dbh, $artistMBIdsWithoutCache[$i], null);
                \Spieldose\Utils::showProgressBar($i + 1, $total, 20, "MBId: " . $artistMBIdsWithoutCache[$i]);
            }
        } else {
            echo "none found" . PHP_EOL;
        }

        echo "Checking artists without lastfm cached data... ";
        $artistsWithoutLastFMCache = \Spieldose\Scraper\Artist\Scraper::getArtistsWithoutLastFMCache($dbh, true);
        $total = count($artistsWithoutLastFMCache);
        if ($total > 0) {
            echo sprintf(" %d found%s", $total, PHP_EOL);
            \Spieldose\Utils::showProgressBar(1, $total, 20, sprintf("Name: %s (%s)", $artistsWithoutLastFMCache[0]->name, $artistsWithoutLastFMCache[0]->mbId));
            for ($i = 0; $i < $total; $i++) {
                if ($i != 0) {
                    sleep(SECONDS_BETWEEN_API_SCRAPS); // wait between queries for prevent too much remote api requests in small amount of time and get banned
                }
                \Spieldose\Scraper\Artist\Scraper::scrapLastFM($logger, $dbh, $settings["lastFMAPIKey"], $artistsWithoutLastFMCache[$i]->mbId, $artistsWithoutLastFMCache[$i]->name);
                \Spieldose\Utils::showProgressBar($i + 1, $total, 20, sprintf("Name: %s (%s)", $artistsWithoutLastFMCache[$i]->name, $artistsWithoutLastFMCache[$i]->mbId));
            }
        } else {
            echo "none found" . PHP_EOL;
        }

        echo "Checking artists without wikipedia cached data... ";
        $musicBrainzArtistsMBIdsWithoutWikipediaCache = \Spieldose\Scraper\Artist\Scraper::getMusicBrainzArtistsWithoutWikipediaCache($dbh, true);
        $total = count($musicBrainzArtistsMBIdsWithoutWikipediaCache);
        if ($total > 0) {
            echo sprintf(" %d found%s", $total, PHP_EOL);
            \Spieldose\Utils::showProgressBar(1, $total, 20, sprintf("MBId: %s", $musicBrainzArtistsMBIdsWithoutWikipediaCache[0]));
            for ($i = 0; $i < $total; $i++) {
                if ($i != 0) {
                    sleep(SECONDS_BETWEEN_API_SCRAPS); // wait between queries for prevent too much remote api requests in small amount of time and get banned
                }
                \Spieldose\Scraper\Artist\Scraper::scrapWiki($logger, $dbh, $musicBrainzArtistsMBIdsWithoutWikipediaCache[$i], null);
                \Spieldose\Utils::showProgressBar($i + 1, $total, 20, sprintf("MBId: %s", $musicBrainzArtistsMBIdsWithoutWikipediaCache[$i]));
            }
        } else {
            echo "none found" . PHP_EOL;
        }

        echo "Checking releases without musicbrainz cached data... ";
        $releaseMBIdsWithoutCache = \Spieldose\Scraper\Release\Scraper::getMusicBrainzReleaseIdsWithoutCache($dbh, true);
        $total = count($releaseMBIdsWithoutCache);
        if ($total > 0) {
            echo sprintf(" %d found%s", $total, PHP_EOL);
            \Spieldose\Utils::showProgressBar(1, $total, 20, "MBId: " . $releaseMBIdsWithoutCache[0]);
            for ($i = 0; $i < $total; $i++) {
                if ($i != 0) {
                    sleep(SECONDS_BETWEEN_API_SCRAPS); // wait between queries for prevent too much remote api requests in small amount of time and get banned
                }
                \Spieldose\Scraper\Release\Scraper::scrap($logger, $dbh, $releaseMBIdsWithoutCache[$i]);
                \Spieldose\Utils::showProgressBar($i + 1, $total, 20, "MBId: " . $releaseMBIdsWithoutCache[$i]);
            }
        } else {
            echo "none found" . PHP_EOL;
        }

        exit;

        $cmdLine = new \Spieldose\CmdLine("", array("all", "artists", "albums", "force", "artiststhumbnails"));
        $scrapArtists = $cmdLine->hasParam("artists") || $cmdLine->hasParam("all");
        $scrapAlbums = $cmdLine->hasParam("albums") || $cmdLine->hasParam("all");
        $scrapArtistsThumbnails = $cmdLine->hasParam("artiststhumbnails");
        $force = $cmdLine->hasParam("force");
        if ($scrapArtists || $scrapAlbums || $scrapArtistsThumbnails) {
            $scraper = new \Spieldose\Scraper($db, $logger, \aportela\MusicBrainzWrapper\APIFormat::JSON);
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
                        \Spieldose\Utils::showProgressBar($i + 1, $totalArtistNames, 20, "Name: " . $artistNames[$i]);
                    }
                } else {
                    echo sprintf("No artist names without MusicBrainzId found%s", PHP_EOL);
                }
                $artistMBIds = !$force ? $scraper->getArtistMusicBrainzIdsWithoutCachedMetadata() : $scraper->getArtistMusicBrainzIds();
                $totalArtistMBIds = count($artistMBIds);
                if ($totalArtistMBIds > 0) {
                    echo sprintf("Processing %d artist without MusicBrainz cached metadata%s", $totalArtistMBIds, PHP_EOL);
                    for ($i = 0; $i < $totalArtistMBIds; $i++) {
                        try {
                            $mbArtist = $scraper->getArtistMusicBrainzMetadata($artistMBIds[$i]);
                            if (!empty($mbArtist->mbId) && !empty($mbArtist->name)) {
                                $savedImage = false;
                                $hasLastFMRelation = false;
                                $scraper->saveArtistMusicBrainzCachedMetadata($mbArtist);
                                $artistImageURLs = $mbArtist->getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType::IMAGE);
                                if (count($artistImageURLs) > 0) {
                                    if (str_starts_with($artistImageURLs[0], "https://commons.wikimedia.org/wiki/File:")) {
                                        $fields = explode("/File:", $artistImageURLs[0]);
                                        if (count($fields) == 2) {
                                            $f = new \aportela\MediaWikiWrapper\Wikipedia\File($logger, \aportela\MediaWikiWrapper\APIType::REST);
                                            $f->setTitle($fields[1]);
                                            $f->get();
                                            $url = $f->getURL(\aportela\MediaWikiWrapper\FileInformationType::ORIGINAL);
                                            if (!empty($url)) {
                                                $scraper->saveArtistImage($mbArtist->mbId, $url);
                                                $savedImage = true;
                                            }
                                        }
                                    } else {
                                        $scraper->saveArtistImage($mbArtist->mbId, $artistImageURLs[0]);
                                        $savedImage = true;
                                    }
                                } else {
                                    $artistLastFMURLs = $mbArtist->getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_LASTFM);
                                    if (count($artistLastFMURLs) > 0) {
                                        $hasLastFMRelation = true;
                                        $lastFMArtist = new \aportela\LastFMWrapper\Artist($logger, \aportela\LastFMWrapper\APIFormat::JSON, $settings["lastFMAPIKey"]);
                                        $url = $lastFMArtist->getImageFromArtistPageURL($artistLastFMURLs[0]);
                                        if (!empty($url)) {
                                            $scraper->saveArtistImage($mbArtist->mbId, $url);
                                        }
                                    }
                                }
                                // TODO: check not found exceptions
                                $wikipediaPageURLs = $mbArtist->getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_WIKIPEDIA);
                                if (count($wikipediaPageURLs) > 0) {
                                    $wikiPage = new \aportela\MediaWikiWrapper\Wikipedia\Page($logger, \aportela\MediaWikiWrapper\APIType::REST);
                                    $wikiPage->setURL($wikipediaPageURLs[0]);
                                    $html = $wikiPage->getHTML();
                                    $scraper->saveArtistWikipediaCachedMetadata($artistMBIds[$i], $html);
                                } else {
                                    $wikiDataPageURLs = $mbArtist->getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_WIKIDATA);
                                    if (count($wikiDataPageURLs) > 0) {
                                        $item = new \aportela\MediaWikiWrapper\Wikidata\Item($logger, \aportela\MediaWikiWrapper\APIType::REST);
                                        $item->setURL($wikiDataPageURLs[0]);
                                        $title = $item->getWikipediaTitle(\aportela\MediaWikiWrapper\Language::ENGLISH);
                                        if (!empty($title)) {
                                            $wikiPage = new \aportela\MediaWikiWrapper\Wikipedia\Page($logger, \aportela\MediaWikiWrapper\APIType::REST);
                                            $wikiPage->setTitle($title);
                                            $html = $wikiPage->getHTML();
                                            $scraper->saveArtistWikipediaCachedMetadata($artistMBIds[$i], $html);
                                        }
                                    }
                                }


                                if (!empty($settings["lastFMAPIKey"])) {
                                    // TODO: check not found exceptions
                                    $lastFMArtist = new \aportela\LastFMWrapper\Artist($logger, \aportela\LastFMWrapper\APIFormat::JSON, $settings["lastFMAPIKey"]);
                                    $lastFMArtist->get($mbArtist->name);
                                    if (isset($lastFMArtist->bio) && isset($lastFMArtist->bio->summary) && isset($lastFMArtist->bio->content)) {
                                        $scraper->saveArtistLastFMCachedMetadata($artistMBIds[$i], $lastFMArtist->bio->summary, $lastFMArtist->bio->content);
                                        if (!$hasLastFMRelation) {
                                            // TODO: add lastfm relation & save
                                        }
                                    }
                                    if (!$savedImage) {
                                        $url = $lastFMArtist->getImageFromArtistPageURL($lastFMArtist->url);
                                        if (!empty($url)) {
                                            $scraper->saveArtistImage($mbArtist->mbId, $url);
                                            $savedImage = true;
                                        }
                                    }
                                }
                            }
                        } catch (\Throwable $e) {
                            print_r($e->getMessage());
                        } finally {
                            sleep(1); // wait 1 second between queries for prevent too much remote api requests in small amount of time and get banned
                            \Spieldose\Utils::showProgressBar($i + 1, $totalArtistMBIds, 20, "MusicBrainzId: " . $artistMBIds[$i] . ($mbArtist->name ? " - " . $mbArtist->name : ""));
                        }
                    }
                } else {
                    echo sprintf("All Artist MusicBrainz metadata is cached%s", PHP_EOL);
                }
            }
            if ($scrapArtistsThumbnails) {
                if (!file_exists($settings['thumbnails']['artists']['basePath'])) {
                    if (!mkdir($settings['thumbnails']['artists']['basePath'], 0750, true)) {
                        $logger->critical("Error creating artist thumbnail basePath: " . $settings['thumbnails']['artists']['basePath']);
                    }
                }
                if (!file_exists($settings['thumbnails']['albums']['basePath'])) {
                    if (!mkdir($settings['thumbnails']['albums']['basePath'], 0750, true)) {
                        $logger->critical("Error creating album thumbnail basePath: " . $settings['thumbnails']['albums']['basePath']);
                    }
                }
                $artistImageURLs = $scraper->getArtistsImageURLs();
                $totalArtistsImageURLs = count($artistImageURLs);
                echo sprintf("Processing %d artist images%s", $totalArtistsImageURLs, PHP_EOL);
                $thumbnail = new \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail($logger, $settings['thumbnails']['artists']['basePath']);
                for ($i = 0; $i < $totalArtistsImageURLs; $i++) {
                    foreach (array_keys($settings['thumbnails']['artists']['sizes']) as $size) {
                        $thumbnail->setQuality($settings['thumbnails']['artists']['sizes'][$size]['quality']);
                        $thumbnail->setDimensions($settings['thumbnails']['artists']['sizes'][$size]['width'], $settings['thumbnails']['artists']['sizes'][$size]['height']);
                        $thumbnail->getFromRemoteURL($artistImageURLs[$i], $force);
                    }
                    \Spieldose\Utils::showProgressBar($i + 1, $totalArtistsImageURLs, 20, "URL: " . $artistImageURLs[$i]);
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
                        \Spieldose\Utils::showProgressBar($i + 1, $totalAlbums, 20, "Name: " . $albums[$i]->album . " / " . $albums[$i]->artist);
                    }
                } else {
                    echo sprintf("No albums without MusicBrainzId found%s", PHP_EOL);
                }

                $albumMBIds = !$force ? $scraper->getAlbumMusicBrainzIdsWithoutCachedMetadata() : $scraper->getAlbumMusicBrainzIds();
                $totalAlbumMBIds = count($albumMBIds);
                if ($totalAlbumMBIds > 0) {
                    echo sprintf("Processing %d albums without MusicBrainz cached metadata%s", $totalAlbumMBIds, PHP_EOL);
                    for ($i = 0; $i < $totalAlbumMBIds; $i++) {
                        $mbAlbum = $scraper->getAlbumMusicBrainzMetadata($albumMBIds[$i]);
                        if (!empty($mbAlbum->mbId) && !empty($mbAlbum->title)) {
                            $scraper->saveAlbumMusicBrainzCachedMetadata($mbAlbum);
                        }
                        sleep(1); // wait 1 second between queries for prevent too much remote api requests in small amount of time and get banned
                        \Spieldose\Utils::showProgressBar($i + 1, $totalAlbumMBIds, 20, "MusicBrainzId: " . $albumMBIds[$i]);
                    }
                } else {
                    echo sprintf("All Album MusicBrainz metadata is cached%s", PHP_EOL);
                }
            }
        } else {
            echo "No required params found." . PHP_EOL;
            echo "Scrap & cache pending/missing artists:" . PHP_EOL;
            echo "\tphp " . $argv[0] . " --artists" . PHP_EOL;
            echo "Scrap & cache pending/missing albums:" . PHP_EOL;
            echo "\tphp " . $argv[0] . " --albums" . PHP_EOL;
            echo "Scrap & cache pending/missing artists thumbnails:" . PHP_EOL;
            echo "\tphp " . $argv[0] . " --artists-thumbnails" . PHP_EOL;
        }
    } catch (\Exception $e) {
        echo "Uncaught exception: " . $e->getMessage() . PHP_EOL;
        $logger->critical("Uncaught exception: " . $e->getMessage());
    }
}

<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

define("PROGRESSBAR_LENGTH", 16);

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

echo "Spieldose scanner" . PHP_EOL;

$logger = $container->get(\Spieldose\Logger\ScannerLogger::class);

$logger->info("Scan started");

$settings = new \Spieldose\Settings();

$setup = new \Spieldose\Setup($logger);

echo "[?] Checking php required extensions...";
if ($setup->checkRequiredPHPExtensions()) {
    echo " success!" . PHP_EOL;
} else {
    $missingPHPExtensions = $setup->getMissingPHPExtensions();
    echo " error! - missing extensions: " . implode(",", $missingPHPExtensions) . PHP_EOL;
    $logger->error("Missing php required extensions", $missingPHPExtensions);
    exit(1);
}

$dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
    echo "[E] Error getting database handler from container" . PHP_EOL;
    $logger->error("Error getting database handler from container");
    exit(1);
}

try {
    $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
    if ($dbh->getCurrentSchemaVersion() < $dbh->getUpgradeSchemaVersion()) {
        echo "New database version available, an upgrade is required before continue." . PHP_EOL;
        exit;
    }
    $cmdLine = new \Spieldose\CmdLine("", array("force", "addLibraryPath:", "removeLibraryPath:", "processID3Queue", "scrapMusicBrainzArtistNamesWithoutMBId", "scrapMusicBrainzReleaseArtistNamesWithoutMBId", "scrapMusicBrainzArtistCache", "scrapMusicBrainzReleaseCache", "scrapLastFMArtistCache", "scrapLastFMAlbumCache", "scrapWikipediaArtistCache", "scrapLyrics", "showProgressBar", "clean"));
    if ($cmdLine->hasOptions()) {
        $showProgressBar = $cmdLine->hasParam("showProgressBar");
        $force = $cmdLine->hasParam("force");

        if ($cmdLine->hasParam("addLibraryPath")) {
            $newLibraryPath = realpath($cmdLine->getParamValue("addLibraryPath"));
            echo "Setting library path: " . $newLibraryPath . PHP_EOL;
            if (file_exists($newLibraryPath)) {
                $libraryManager = new \Spieldose\Library\Manager($dbh, $logger);
                if ($libraryManager->isPathContainedOnCurrentLibraryPaths($newLibraryPath)) {
                    echo "\tERROR: path is contained on existing library path" . PHP_EOL;
                } else {
                    $pathId = $libraryManager->addLibraryPath($newLibraryPath);
                    echo "Starting library scanner:" . PHP_EOL;
                    $libraryScanner = new \Spieldose\Library\Scanner\LibraryScanner($dbh, $logger);

                    $albumCoverPathValidFilenamesPattern = $settings->getAlbumCoverPathValidFilenamesPattern();
                    if (!empty($albumCoverPathValidFilenamesPattern)) {
                        $libraryScanner->setValidCoverFilenamesPattern($albumCoverPathValidFilenamesPattern);
                    }
                    $totalScanTime = $libraryScanner->scanLibraryPath(
                        $pathId,
                        $newLibraryPath,
                        true,
                        $force,
                        function ($directories, $total, $index) use ($showProgressBar) {
                            if ($showProgressBar) {
                                echo sprintf(" - Directory %d/%d: %s%s", $index + 1, $total, $directories[$index], PHP_EOL);
                            } else {
                                echo sprintf(" - Directory %d/%d: %s%s", $index + 1, $total, $directories[$index], PHP_EOL);
                            }
                        },
                        function ($currentDirectoryFiles, $total, $index) use ($showProgressBar) {
                            if ($showProgressBar) {
                                \Spieldose\Utils::showProgressBar($index + 1, $total, PROGRESSBAR_LENGTH, sprintf(" - Scanning %d directory file/s:", $total), "- Current file: " . basename($currentDirectoryFiles[$index]));
                            } else {
                                if ($index == 0) {
                                    echo " - Scanning {$total} directory file/s: ";
                                }
                                echo ".";
                                if ($index == $total - 1) {
                                    echo PHP_EOL;
                                }
                            }
                        }
                    );
                    echo sprintf("Library scan finished (total scan time: %.2f seconds)%s", $totalScanTime, PHP_EOL);
                }
            } else {
                echo "- ERROR: path not found on local filesystem" . PHP_EOL;
                $logger->error("Invalid music path / path not found", [$newLibraryPath]);
            }
        }
        if ($cmdLine->hasParam("removeLibraryPath")) {
            $path = realpath($cmdLine->getParamValue("removeLibraryPath"));
            echo "Removing library path: " . $path . PHP_EOL;
            $libraryManager = new \Spieldose\Library\Manager($dbh, $logger);
            if ($libraryManager->removeLibraryPath($path)) {
                echo " - Removed successfully" . PHP_EOL;
            } else {
                echo " - Error removing library path (path not found)" . PHP_EOL;
            }
        }
        if ($cmdLine->hasParam("processID3Queue")) {
            echo "Checking id3 queue:" . PHP_EOL;
            $id3Scanner = new \Spieldose\Library\Scanner\ID3Scanner($dbh, $logger);
            $totalScanTime = $id3Scanner->processPendingQueue(
                function ($queuedItems, $total, $index) use ($showProgressBar) {
                    if ($showProgressBar) {
                        \Spieldose\Utils::showProgressBar($index + 1, $total, PROGRESSBAR_LENGTH, "", "- Current file: {$queuedItems[$index]->fullPath}");
                    } else {
                        if ($index == 0) {
                            echo " - Processing ({$total}) queued items/s:";
                        }
                        echo ".";
                        if ($index == $total - 1) {
                            echo PHP_EOL;
                        }
                    }
                },
                function () {
                    echo " [!] Queue is empty" . PHP_EOL;
                }
            );
            echo sprintf("ID3 process queue finished (total process time: %.2f seconds)%s", $totalScanTime, PHP_EOL);
        }
        if ($cmdLine->hasParam("scrapMusicBrainzArtistNamesWithoutMBId")) {
            echo "Starting Musicbrainz Artist Scrapper (Searching artists with name && without mbId):" . PHP_EOL;
            $cache = new \aportela\SimpleFSCache\Cache($logger, $settings->getCachePath("MusicBrainz"), null, \aportela\SimpleFSCache\CacheFormat::JSON,);
            $mbArtistScanner = new \Spieldose\Library\Scraper\MusicBrainz\ArtistScraper($dbh, $logger, $cache);
            $totalScrapTime = $mbArtistScanner->scrapArtistsWithoutMusicBrainzId(
                function ($items, $total, $index) use ($showProgressBar) {
                    if ($showProgressBar) {
                        \Spieldose\Utils::showProgressBar($index + 1, $total, PROGRESSBAR_LENGTH, " - Searching ({$total}) artist/s", "- Artist name: {$items[$index]->artistName}");
                    } else {
                        if ($index == 0) {
                            echo " - Searching {$total} artist/s: ";
                        }
                        echo ".";
                        if ($index == $total - 1) {
                            echo PHP_EOL;
                        }
                    }
                }
            );
            echo sprintf("MusicBrainz artist search scrap process finished (total process time: %.2f seconds)%s", $totalScrapTime, PHP_EOL);
        }
        if ($cmdLine->hasParam("scrapMusicBrainzReleaseArtistNamesWithoutMBId")) {
            echo "Starting Musicbrainz Release Artist Scrapper (Searching release artists with name && without mbId):" . PHP_EOL;
            $cache = new \aportela\SimpleFSCache\Cache($logger, $settings->getCachePath("MusicBrainz"), null, \aportela\SimpleFSCache\CacheFormat::JSON,);
            $mbArtistScanner = new \Spieldose\Library\Scraper\MusicBrainz\ArtistScraper($dbh, $logger, $cache);
            $totalScrapTime = $mbArtistScanner->scrapReleaseArtistsWithoutMusicBrainzId(
                function ($items, $total, $index) use ($showProgressBar) {
                    if ($showProgressBar) {
                        \Spieldose\Utils::showProgressBar($index + 1, $total, PROGRESSBAR_LENGTH, " - Searching ({$total}) artist/s", "- Artist name: {$items[$index]->artistName}");
                    } else {
                        if ($index == 0) {
                            echo " - Searching {$total} artist/s: ";
                        }
                        echo ".";
                        if ($index == $total - 1) {
                            echo PHP_EOL;
                        }
                    }
                }
            );
            echo sprintf("MusicBrainz release artist search scrap process finished (total process time: %.2f seconds)%s", $totalScrapTime, PHP_EOL);
        }
        if ($cmdLine->hasParam("scrapMusicBrainzArtistCache")) {
            echo "Starting Musicbrainz Artist Scrapper (Artists without MusicBrainz cache):" . PHP_EOL;
            $cache = new \aportela\SimpleFSCache\Cache($logger, $settings->getCachePath("MusicBrainz"), null, \aportela\SimpleFSCache\CacheFormat::JSON);
            $mbArtistScraper = new \Spieldose\Library\Scraper\MusicBrainz\ArtistScraper($dbh, $logger, $cache);
            $totalScrapTime = $mbArtistScraper->scrapMissingCache(
                function ($mbIds, $total, $index) use ($showProgressBar) {
                    if ($showProgressBar) {
                        \Spieldose\Utils::showProgressBar($index + 1, $total, PROGRESSBAR_LENGTH, " - Caching ({$total}) artist musicbrainz id/s", "- Artist mbId: {$mbIds[$index]}");
                    } else {
                        if ($index == 0) {
                            echo " - Caching {$total} artist musicbrainz id/s: ";
                        }
                        echo ".";
                        if ($index == $total - 1) {
                            echo PHP_EOL;
                        }
                    }
                },
                $force
            );
            echo sprintf("MusicBrainz artist data scrap process finished (total process time: %.2f seconds)%s", $totalScrapTime, PHP_EOL);
        }
        if ($cmdLine->hasParam("scrapMusicBrainzReleaseCache")) {
            echo "Starting Musicbrainz Release Scrapper (Releases without MusicBrainz cache):" . PHP_EOL;
            $cache = new \aportela\SimpleFSCache\Cache($logger, $settings->getCachePath("MusicBrainz"), null, \aportela\SimpleFSCache\CacheFormat::JSON);
            $mbReleaseScraper = new \Spieldose\Library\Scraper\MusicBrainz\ReleaseScraper($dbh, $logger, $cache);
            $totalScrapTime = $mbReleaseScraper->scrapMissingCache(
                function ($mbIds, $total, $index) use ($showProgressBar) {
                    if ($showProgressBar) {
                        \Spieldose\Utils::showProgressBar($index + 1, $total, PROGRESSBAR_LENGTH, " - Caching ({$total}) release musicbrainz id/s", "- Release mbId: {$mbIds[$index]}");
                    } else {
                        if ($index == 0) {
                            echo " - Caching {$total} release musicbrainz id/s: ";
                        }
                        echo ".";
                        if ($index == $total - 1) {
                            echo PHP_EOL;
                        }
                    }
                },
                $force
            );
            echo sprintf("MusicBrainz release data scrap process finished (total process time: %.2f seconds)%s", $totalScrapTime, PHP_EOL);
        }
        if ($cmdLine->hasParam("scrapLastFMArtistCache")) {
            echo "Starting LastFM Artist Scrapper (Artists without LastFM cache):" . PHP_EOL;
            $lastFMAPIKey = $settings->getLastFMAPIKey();
            if (! empty($lastFMAPIKey)) {
                $cache = new \aportela\SimpleFSCache\Cache($logger, $settings->getCachePath("LastFM"), null, \aportela\SimpleFSCache\CacheFormat::JSON);
                $lastFMArtistScraper = new \Spieldose\Library\Scraper\LastFM\ArtistScraper($dbh, $logger, $lastFMAPIKey, $cache);
                $totalScrapTime = $lastFMArtistScraper->scrapMissingCache(
                    function ($names, $total, $index) use ($showProgressBar) {
                        if ($showProgressBar) {
                            \Spieldose\Utils::showProgressBar($index + 1, $total, PROGRESSBAR_LENGTH, " - Caching ({$total}) artist lastfm name/s", "- Artist name: {$names[$index]}");
                        } else {
                            if ($index == 0) {
                                echo " - Caching {$total} artist lastfm name/s: ";
                            }
                            echo ".";
                            if ($index == $total - 1) {
                                echo PHP_EOL;
                            }
                        }
                    },
                    $force
                );
                echo sprintf("LastFM artist data scrap process finished (total process time: %.2f seconds)%s", $totalScrapTime, PHP_EOL);
            } else {
                echo "ERROR - Missing LastFM API KEY " . PHP_EOL;
            }
        }
        if ($cmdLine->hasParam("scrapLastFMAlbumCache")) {
            echo "Starting LastFM Album Scrapper (Albums without LastFM cache):" . PHP_EOL;
            $lastFMAPIKey = $settings->getLastFMAPIKey();
            if (! empty($lastFMAPIKey)) {
                $cache = new \aportela\SimpleFSCache\Cache($logger, $settings->getCachePath("LastFM"), null, \aportela\SimpleFSCache\CacheFormat::JSON);
                $lastFMAlbumScraper = new \Spieldose\Library\Scraper\LastFM\AlbumScraper($dbh, $logger, $lastFMAPIKey, $cache);
                $totalScrapTime = $lastFMAlbumScraper->scrapMissingCache(
                    function ($items, $total, $index) use ($showProgressBar) {
                        if ($showProgressBar) {
                            \Spieldose\Utils::showProgressBar($index + 1, $total, PROGRESSBAR_LENGTH, " - Caching ({$total}) album lastfm item/s",  "- Album: {$items[$index]->album} - artist: {$items[$index]->artist}");
                        } else {
                            if ($index == 0) {
                                echo " - Caching {$total} album lastfm item/s: ";
                            }
                            echo ".";
                            if ($index == $total - 1) {
                                echo PHP_EOL;
                            }
                        }
                    },
                    $force
                );
                echo sprintf("LastFM album data scrap process finished (total process time: %.2f seconds)%s", $totalScrapTime, PHP_EOL);
            } else {
                echo "ERROR - Missing LastFM API KEY " . PHP_EOL;
            }
        }
        if ($cmdLine->hasParam("scrapWikipediaArtistCache")) {
            echo "Starting Wikipedia Artist Scrapper (Wikipedia artist page without cache):" . PHP_EOL;
            $cache = new \aportela\SimpleFSCache\Cache($logger, $settings->getCachePath("Wikipedia"), null, \aportela\SimpleFSCache\CacheFormat::HTML);
            $wikipediaScraper = new \Spieldose\Library\Scraper\Wikipedia\ArtistScraper($dbh, $logger, $cache);
            $totalScrapTime = $wikipediaScraper->scrapMissingCache(
                function ($artists, $total, $index) use ($showProgressBar) {
                    if ($showProgressBar) {
                        \Spieldose\Utils::showProgressBar($index + 1, $total, PROGRESSBAR_LENGTH, " - Caching ({$total}) artist/s",  "- Artist: {$artists[$index]->name}");
                    } else {
                        if ($index == 0) {
                            echo " - Caching {$total} artist/s: ";
                        }
                        echo ".";
                        if ($index == $total - 1) {
                            echo PHP_EOL;
                        }
                    }
                },
                $force
            );
            echo sprintf("Wikipedia artist scrap process finished (total process time: %.2f seconds)%s", $totalScrapTime, PHP_EOL);
        }
        if ($cmdLine->hasParam("scrapLyrics")) {
            echo "Starting Lyrics Scrapper (Lyrics without cache):" . PHP_EOL;
            $cache = new \aportela\SimpleFSCache\Cache($logger, $settings->getCachePath("Lyrics"), null, \aportela\SimpleFSCache\CacheFormat::TXT);
            $lyricsScraper = new \Spieldose\Library\Scraper\Lyrics($dbh, $logger, $cache);
            $totalScrapTime = $lyricsScraper->scrapMissingCache(
                function ($tracks, $total, $index) use ($showProgressBar) {
                    if ($showProgressBar) {
                        \Spieldose\Utils::showProgressBar($index + 1, $total, PROGRESSBAR_LENGTH, " - Caching ({$total}) track/s",  "- Artist: {$tracks[$index]->artist} - Title: {$tracks[$index]->title}");
                    } else {
                        if ($index == 0) {
                            echo " - Caching {$total} tracks/s: ";
                        }
                        echo ".";
                        if ($index == $total - 1) {
                            echo PHP_EOL;
                        }
                    }
                },
                $force
            );
            echo sprintf("Lyrics track scrap process finished (total process time: %.2f seconds)%s", $totalScrapTime, PHP_EOL);
        }
        if ($cmdLine->hasParam("clean")) {
            echo "Cleaning database...";
            $libraryManager = new \Spieldose\Library\Manager($dbh, $logger);
            $libraryDirectoryFiles = $libraryManager->getAllLibraryPathDirectoryFiles();
            $totalFiles = count($libraryDirectoryFiles);
            echo " " . $totalFiles . " files found" . PHP_EOL;
            $totalDeleted = 0;
            for ($i = 0; $i < $totalFiles; $i++) {
                if (! file_exists($libraryDirectoryFiles[$i]->fullPath)) {

                    $libraryManager->removeLibraryPathDirectoryFile($libraryDirectoryFiles[$i]->id);
                    $totalDeleted++;
                }
                \Spieldose\Utils::showProgressBar($i + 1, $totalFiles, PROGRESSBAR_LENGTH, $libraryDirectoryFiles[$i]->fullPath);
            }
            echo "Datatabase clean finished. ";
            if ($totalDeleted > 0) {
                echo "Total deleted files: " . $totalDeleted . PHP_EOL;
            } else {
                echo "No orphan/deleted files found" . PHP_EOL;
            }
        }
    } else {
        echo "[!] No required params found (can be combined)." . PHP_EOL;
        echo "- Add / refresh music path:" . PHP_EOL;
        echo "\tphp " . $argv[0] . " --addLibraryPath <YOUR_MUSIC_PATH>" . PHP_EOL;
        echo "- Remove music path:" . PHP_EOL;
        echo "\tphp " . $argv[0] . " --removeLibraryPath <YOUR_MUSIC_PATH>" . PHP_EOL;
        echo "- Scan pending id3 tags:" . PHP_EOL;
        echo "\tphp " . $argv[0] . " --processID3Queue" . PHP_EOL;
        echo "- Search on MusicBrainz artists without MBId:" . PHP_EOL;
        echo "\tphp " . $argv[0] . " --scrapMusicBrainzArtistNamesWithoutMBId" . PHP_EOL;
        echo "- Search on MusicBrainz release artists without MBId:" . PHP_EOL;
        echo "\tphp " . $argv[0] . " --scrapMusicBrainzReleaseArtistNamesWithoutMBId" . PHP_EOL;
        echo "- Get (pending) MusicBrainz artist cache:" . PHP_EOL;
        echo "\tphp " . $argv[0] . " --scrapMusicBrainzArtistCache" . PHP_EOL;
        echo "- Get (pending) MusicBrainz release (album) cache:" . PHP_EOL;
        echo "\tphp " . $argv[0] . " --scrapMusicBrainzReleaseCache" . PHP_EOL;
        echo "- Get (pending) LastFM artist cache:" . PHP_EOL;
        echo "\tphp " . $argv[0] . " --scrapLastFMArtistCache" . PHP_EOL;
        echo "- Get (pending) LastFM album cache:" . PHP_EOL;
        echo "\tphp " . $argv[0] . " --scrapLastFMAlbumCache" . PHP_EOL;
        echo "- Get (pending) Wikipedia artist cache:" . PHP_EOL;
        echo "\tphp " . $argv[0] . " --scrapWikipediaArtistCache" . PHP_EOL;
        echo "- Get (pending) Lyrics cache:" . PHP_EOL;
        echo "\tphp " . $argv[0] . " --scrapLyrics" . PHP_EOL;
        echo "Clean database (deleted/orphaned items):" . PHP_EOL;
        echo "\tphp " . $argv[0] . " --clean" . PHP_EOL;

        echo "[I] Optional params (combine with required params)" . PHP_EOL;
        echo "- Show progress bar:" . PHP_EOL;
        echo "\tphp " . $argv[0] . " --showProgressBar <other params>" . PHP_EOL;
        echo "- Force process again all items (not only pending):" . PHP_EOL;
        echo "\tphp " . $argv[0] . " --force <other params>" . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Uncaught exception: " . $e->getMessage() . PHP_EOL;
    $logger->critical("Uncaught exception: " . $e->getMessage());
}

exit(0);

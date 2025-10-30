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
        $cmdLine = new \Spieldose\CmdLine("", array("force", "addLibraryPath:", "removeLibraryPath:", "processID3Queue", "fixMusicBrainzArtistMBIds", "scrapMusicBrainzArtistNamesWithoutMBId", "scrapMusicBrainzArtistCache", "scrapMusicBrainzReleaseCache", "showProgressBar", "clean"));
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
                        if (!empty($settings["albumCoverPathValidFilenames"])) {
                            $libraryScanner->setValidCoverFilenamesPattern($settings["albumCoverPathValidFilenames"]);
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
                                        echo sprintf(" - Scanning %d directory file/s: ", $total);
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
                                echo sprintf(" - Processing (%d) queued items/s:", $total);
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
            if ($cmdLine->hasParam("fixMusicBrainzArtistMBIds")) {
                echo "Fixing missing MusicBrainz ids: ";
                $id3Scanner->fixMissingArtistMBIdsWithExistent();
                echo "ok!" . PHP_EOL;
            }
            if ($cmdLine->hasParam("scrapMusicBrainzArtistNamesWithoutMBId")) {
                echo "Starting Musicbrainz Artist Scrapper (Searching artists with name && without mbId):" . PHP_EOL;
                $mbArtistScanner = new \Spieldose\Library\Scraper\MusicBrainzArtistScraper($dbh, $logger, $settings["cache"]["MusicBrainzCachePath"]);
                $totalScrapTime = $mbArtistScanner->scrapArtistsWithoutMusicBrainzId(
                    function ($artistNames, $total, $index) use ($showProgressBar) {
                        if ($showProgressBar) {
                            \Spieldose\Utils::showProgressBar($index + 1, $total, PROGRESSBAR_LENGTH, sprintf(" - Searching (%d) artist/s", $total), "- Artist name: " . $artistNames[$index]);
                        } else {
                            if ($index == 0) {
                                echo sprintf(" - Searching %d artist/s: ", $total);
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
            if ($cmdLine->hasParam("scrapMusicBrainzArtistCache")) {
                echo "Starting Musicbrainz Artist Scrapper (Artists without MusicBrainz cache):" . PHP_EOL;
                $mbArtistScraper = new \Spieldose\Library\Scraper\MusicBrainz\ArtistScraper($dbh, $logger, $settings["cache"]["MusicBrainzCachePath"], $force);
                $totalScrapTime = $mbArtistScraper->scrapMissingCache(
                    function ($mbIds, $total, $index) use ($showProgressBar) {
                        if ($showProgressBar) {
                            \Spieldose\Utils::showProgressBar($index + 1, $total, PROGRESSBAR_LENGTH, sprintf(" - Caching (%d) artist musicbrainz id/s", $total), "- Artist mbId: " . $mbIds[$index]);
                        } else {
                            if ($index == 0) {
                                echo sprintf(" - Caching %d artist musicbrainz id/s: ", $total);
                            }
                            echo ".";
                            if ($index == $total - 1) {
                                echo PHP_EOL;
                            }
                        }
                    }
                );
                echo sprintf("MusicBrainz artist data scrap process finished (total process time: %.2f seconds)%s", $totalScrapTime, PHP_EOL);
            }
            if ($cmdLine->hasParam("scrapMusicBrainzReleaseCache")) {
                echo "Starting Musicbrainz Release Scrapper (Releases without MusicBrainz cache):" . PHP_EOL;
                $mbReleaseScraper = new \Spieldose\Library\Scraper\MusicBrainz\ReleaseScraper($dbh, $logger, $settings["cache"]["MusicBrainzCachePath"], $force);
                $totalScrapTime = $mbReleaseScraper->scrapMissingCache(
                    function ($mbIds, $total, $index) use ($showProgressBar) {
                        if ($showProgressBar) {
                            \Spieldose\Utils::showProgressBar($index + 1, $total, PROGRESSBAR_LENGTH, sprintf(" - Caching (%d) release musicbrainz id/s", $total), "- Release mbId: " . $mbIds[$index]);
                        } else {
                            if ($index == 0) {
                                echo sprintf(" - Caching %d release musicbrainz id/s: ", $total);
                            }
                            echo ".";
                            if ($index == $total - 1) {
                                echo PHP_EOL;
                            }
                        }
                    }
                );
                echo sprintf("MusicBrainz release data scrap process finished (total process time: %.2f seconds)%s", $totalScrapTime, PHP_EOL);

                /*
                    if (! empty($settings['lastFMAPIKey'])) {
                        $lastFMArtist = new \aportela\LastFMWrapper\Artist($logger, \aportela\LastFMWrapper\APIFormat::JSON, $settings['lastFMAPIKey']);
                        try {
                            $lastFMArtist->get($mbArtist->name ?? $artistNames[$i]);
                            $libraryManager->saveLastFMCacheArtist($lastFMArtist);
                        } catch (\Throwable $e) {
                        }
                    }
                    */
                /*
                    $wikipediaArtist = new \aportela\MediaWikiWrapper\Wikipedia\Page($logger);
                    $artistWikiPages = $mbArtist->getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_WIKIPEDIA);
                    if (count($artistWikiPages) > 0) {
                        print_r($artistWikiPages);
                        $wikipediaArtist->setURL($artistWikiPages[0]);
                        try {
                            $html = $wikipediaArtist->getHTML();
                            if (! empty($html)) {
                                $libraryManager->saveMBCacheArtist($mbArtist);
                            }
                        } catch (\Throwable $e) {
                        }
                    }
                        */
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
            echo "No required params found." . PHP_EOL;
            /*
            echo "Scan / update music path:" . PHP_EOL;
            echo "\tphp " . $argv[0] . " --addLibraryPath <YOUR_MUSIC_PATH>" . PHP_EOL;
            echo "Clean database (deleted/orphaned items):" . PHP_EOL;
            echo "\tphp " . $argv[0] . " --clean" . PHP_EOL;
            */
        }
    } catch (\Exception $e) {
        echo "Uncaught exception: " . $e->getMessage() . PHP_EOL;
        $logger->critical("Uncaught exception: " . $e->getMessage());
    }
}

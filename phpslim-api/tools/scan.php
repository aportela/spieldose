<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

const SECONDS_BETWEEN_API_SCRAPS = 1;

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
        $cmdLine = new \Spieldose\CmdLine("", array("addLibraryPath:", "processID3Queue", "clean", "scrapMB"));
        if ($cmdLine->hasOptions()) {
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
                        $libraryScanner->scanLibraryPath(
                            $pathId,
                            $newLibraryPath,
                            true,
                            function ($directories, $total, $index) {
                                echo "- Scanning directory " . $directories[$index] . PHP_EOL;
                            },
                            function ($currentDirectoryFiles, $total, $index) {
                                \Spieldose\Utils::showProgressBar($index + 1, $total, 20, $currentDirectoryFiles[$index]);
                            }
                        );
                    }
                } else {
                    echo "- ERROR: path not found on local filesystem" . PHP_EOL;
                    $logger->error("Invalid music path / path not found", [$newLibraryPath]);
                }
            }
            if ($cmdLine->hasParam("processID3Queue")) {
                echo "Processing id3 queue:" . PHP_EOL;
                $id3Scanner = new \Spieldose\Library\Scanner\ID3Scanner($dbh, $logger);
                $id3Scanner->processPendingQueue(
                    function ($queuedItems, $total, $index) {
                        \Spieldose\Utils::showProgressBar($index + 1, $total, 20, $queuedItems[$index]->fullPath);
                    },
                );
                $id3Scanner->fixMissingArtistMBIdsWithExistent();
            }
            if ($cmdLine->hasParam("scrapMB")) {

                echo "Musicbrainz Scrapper starting..." . PHP_EOL;
                $mbArtistScanner = new \Spieldose\Library\Scraper\MusicBrainzArtistScraper($dbh, $logger, $settings["cache"]["MusicBrainzCachePath"]);
                $mbArtistScanner->scrapArtistsWithoutMusicBrainzId(
                    function ($artistNames, $total, $index) {
                        if ($index == 0) {
                            echo "Scrapping orphaned artist names (without mbId)..." . PHP_EOL;
                        }
                        \Spieldose\Utils::showProgressBar($index + 1, $total, 20, $artistNames[$index]);
                    }
                );
                $mbArtistScanner->scrapMissingCache(
                    function ($mbIds, $total, $index) {
                        if ($index == 0) {
                            echo "Scrapping missing MusicBrainz artist cache..." . PHP_EOL;
                        }
                        \Spieldose\Utils::showProgressBar($index + 1, $total, 20, $mbIds[$index]);
                    }
                );

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
                    \Spieldose\Utils::showProgressBar($i + 1, $totalFiles, 20, $libraryDirectoryFiles[$i]->fullPath);
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
            echo "Scan / update music path:" . PHP_EOL;
            echo "\tphp " . $argv[0] . " --path <YOUR_MUSIC_PATH>" . PHP_EOL;
            echo "Clean database (deleted/orphaned items):" . PHP_EOL;
            echo "\tphp " . $argv[0] . " --clean" . PHP_EOL;
        }
    } catch (\Exception $e) {
        echo "Uncaught exception: " . $e->getMessage() . PHP_EOL;
        $logger->critical("Uncaught exception: " . $e->getMessage());
    }
}

<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

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
        //$scanner = new \Spieldose\Scanner\Scanner($dbh, $logger);
        if (!empty($settings["albumCoverPathValidFilenames"])) {
            // TODO
            //$scanner->setValidCoverFilenames($settings["albumCoverPathValidFilenames"]);
        }
        $cmdLine = new \Spieldose\CmdLine("", array("path:", "processID3Queue", "clean"));
        if ($cmdLine->hasOptions()) {
            if ($cmdLine->hasParam("path")) {
                $newLibraryPath = realpath($cmdLine->getParamValue("path"));
                echo "Setting library path: " . $newLibraryPath . PHP_EOL;
                if (file_exists($newLibraryPath)) {
                    $libraryPath = new \Spieldose\Library\LibraryPath($dbh);
                    if ($libraryPath->isPathContainedOnCurrentPaths($newLibraryPath)) {
                        echo "\tERROR: path is contained on existing library path" . PHP_EOL;
                    } else {
                        $pathId = $libraryPath->addPath($newLibraryPath);
                        echo "Scanning path..." . PHP_EOL;
                        echo "- Id: " . $pathId . PHP_EOL;
                        echo "- Path: " . $newLibraryPath . PHP_EOL;
                        echo "- Propagating DIRECTORY & FILE tables... ";
                        // fill DIRECTORY && FILE tables for this library path
                        $libraryPath->scanPath($pathId, $newLibraryPath);
                        echo "ok!" . PHP_EOL;
                    }
                } else {
                    echo "- ERROR: path not found on local filesystem" . PHP_EOL;
                    //$logger->warning("Invalid music path / path not found");
                }
            }
            if ($cmdLine->hasParam("processID3Queue")) {
                echo "Processing id3 queue...";
                $libraryPath = new \Spieldose\Library\LibraryPath($dbh);
                $queuedItems = $libraryPath->getAllLibraryDirectoryFilesQueuedForID3();
                $totalQueuedItems = count($queuedItems);
                echo " " . $totalQueuedItems . " items found" . PHP_EOL;
                $scanner = new \Spieldose\Library\Scanner($dbh, $logger);
                for ($i = 0; $i < $totalQueuedItems; $i++) {
                    $scanner->scanFile($queuedItems[$i]->id, $queuedItems[$i]->fullPath);
                    \Spieldose\Utils::showProgressBar($i + 1, $totalQueuedItems, 20, $queuedItems[$i]->fullPath);
                }
                echo "ID3 queue processed" . PHP_EOL;
            }
            if ($cmdLine->hasParam("clean")) {
                echo "Cleaning database...";
                $libraryDirectoryFiles = $libraryPath->getAllLibraryDirectoryFiles();
                $totalFiles = count($libraryDirectoryFiles);
                echo " " . $totalFiles . " files found" . PHP_EOL;
                for ($i = 0; $i < $totalFiles; $i++) {
                    if (! file_exists($libraryDirectoryFiles[$i]->fullPath)) {
                    }
                    \Spieldose\Utils::showProgressBar($i + 1, $totalFiles, 20, $libraryDirectoryFiles[$i]->fullPath);
                    usleep(50);
                }
                echo "Datatabase clean finished";
            }
        } else {
            echo "No required params found." . PHP_EOL;
            echo "Scan / update music path:" . PHP_EOL;
            echo "\tphp " . $argv[0] . " --path <YOUR_MUSIC_PATH>" . PHP_EOL;
            echo "Clean database (deleted/orphaned items):" . PHP_EOL;
            echo "\tphp " . $argv[0] . " --clean" . PHP_EOL;
        }
        //print_r($scanner->getPaths());
    } catch (\Exception $e) {
        echo "Uncaught exception: " . $e->getMessage() . PHP_EOL;
        $logger->critical("Uncaught exception: " . $e->getMessage());
    }


    //$scanner->addPath($musicPath);
    /*
                $logger->info("Scanning base path: " . $musicPath);

                $files = \Spieldose\FileSystem::getRecursiveDirectoryFiles($musicPath);
                $totalFiles = count($files);
                echo "Total supported files on path: " . $totalFiles . PHP_EOL;
                $logger->debug("Total supported files on path: " . $totalFiles);
                if ($totalFiles > 0) {
                    echo sprintf("Reading %d files from path: %s%s", $totalFiles, $musicPath, PHP_EOL);
                    $failed = array();
                    for ($i = 0; $i < $totalFiles; $i++) {
                        $scanner->scan(($files[$i]));
                        \Spieldose\Utils::showProgressBar($i + 1, $totalFiles, 20, $files[$i]);
                    }
                }
                echo "Fixing missing artist mbIds with existent data before scrap...";
                $total = $scanner->fixMissingArtistMBIdsWithExistent();
                if ($total > 0) {
                    // TODO: bug -> always return 1 ???
                    echo "total files fixed: " . $total . PHP_EOL;
                } else {
                    echo "no files fixed" . PHP_EOL;
                }
                    */
}

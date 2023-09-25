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
        $db = $container->get(\aportela\DatabaseWrapper\DB::class);
        if ($db->getCurrentSchemaVersion() < $db->getUpgradeSchemaVersion()) {
            echo "New database version available, an upgrade is required before continue." . PHP_EOL;
            exit;
        }
        $scanner = new \Spieldose\Scanner($db, $logger);
        $cmdLine = new \Spieldose\CmdLine("", array("path:", "clean"));
        if ($cmdLine->hasParam("path")) {
            $musicPath = realpath($cmdLine->getParamValue("path"));
            if (file_exists($musicPath)) {
                echo "Scanning base path: " . $musicPath . PHP_EOL;
                $scanner->addPath($musicPath);
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
            } else {
                echo "Invalid music path / path not found" . PHP_EOL;
                $logger->warning("Invalid music path / path not found");
            }
        } else if ($cmdLine->hasParam("clean")) {
            echo "Cleaning database...";
            $scanner->cleanUp();
            echo " ok!";
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

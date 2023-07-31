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
        $cmdLine = new \Spieldose\CmdLine("", array("path:"));
        if ($cmdLine->hasParam("path")) {
            $musicPath = realpath($cmdLine->getParamValue("path"));
            if (file_exists($musicPath)) {
                echo "Scanning base path: " . $musicPath . PHP_EOL;
                $logger->info("Scanning base path: " . $musicPath);
                $files = \Spieldose\FileSystem::getRecursiveDirectoryFiles($musicPath);
                $totalFiles = count($files);
                echo "Total files on path: " . $totalFiles . PHP_EOL;
                $logger->debug("Total files on path: " . $totalFiles);
                if ($totalFiles > 0) {
                    echo sprintf("Reading %d files from path: %s%s", $totalFiles, $musicPath, PHP_EOL);
                    $failed = array();
                    for ($i = 0; $i < $totalFiles; $i++) {
                        $scanner->scan(($files[$i]));
                        \Spieldose\Utils::showProgressBar($i + 1, $totalFiles, 20);
                    }
                }
            } else {
                echo "Invalid music path / path not found" . PHP_EOL;
                $logger->warning("Invalid music path / path not found");
            }
        } else {
            echo "No required params found: --path <YOUR_MUSIC_PATH>" . PHP_EOL;
        }
    } catch (\Exception $e) {
        echo "Uncaught exception: " . $e->getMessage() . PHP_EOL;
        $logger->critical("Uncaught exception: " . $e->getMessage());
    }
}

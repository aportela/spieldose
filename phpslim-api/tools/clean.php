<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

echo "Spieldose cleaner" . PHP_EOL;

$logger = $container->get(\Spieldose\Logger\ScannerLogger::class);

$logger->info("Clean started");

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

        $scraper = new \Spieldose\Scraper($db, $logger);
        $results = $scraper->getAllDatabaseFiles();
        $totalResults = count($results);
        if ($totalResults > 0) {
            echo "Checking filesystem existence & removing orphaned data..." . PHP_EOL;
            $totalDeleted = 0;
            $db->beginTransaction();
            $errors = false;
            try {
                for ($i = 0; $i < $totalResults; $i++) {
                    $path = $results[$i]->path . DIRECTORY_SEPARATOR . $results[$i]->filename;
                    $logger->debug(sprintf("Checking path %d/%d", $i + 1, $totalResults), array($path));
                    if (!file_exists($path)) {
                        $logger->notice("Path not found");
                        $scraper->removeDatabaseReferences($results[$i]->id);
                        $totalDeleted++;
                    } else {
                        $logger->debug("Found path");
                    }
                    \Spieldose\Utils::showProgressBar($i + 1, $totalResults, 20);
                }
                if ($totalDeleted > 0) {
                    echo sprintf("%d missing files removed from database%s", $totalDeleted, PHP_EOL);
                } else {
                    echo "No orphaned data found" . PHP_EOL;
                }
            } catch (\PDOException $e) {
                $errors = true;
                $logger->error("Error cleaning database", array($e->getMessage()));
            }
            if (!$errors) {
                $db->commit();
            } else {
                $db->rollBack();
            }
        } else {
            echo "No data (database empty)" . PHP_EOL;
        }
    } catch (\Exception $e) {
        echo "Uncaught exception: " . $e->getMessage() . PHP_EOL;
        $logger->critical("Uncaught exception: " . $e->getMessage());
    }
}

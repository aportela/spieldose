<?php

    declare(strict_types=1);

    ob_start();

    $app = (require __DIR__ . '/../config/bootstrap.php');

    $container = $app->getContainer();

    $settings = $container->get('settings');

    $missingExtensions = array_diff($settings["phpRequiredExtensions"], get_loaded_extensions());
    if (count($missingExtensions) > 0) {
        echo "Error: missing php extension/s: " . implode(", ", $missingExtensions) . PHP_EOL;
        exit;
    }

    $logger = $container->get(\Monolog\Logger::class);
    $logger->info("Database cleaner started");
    $dbh = new \Spieldose\Database\DB($container->get(PDO::class), $container->get(\Monolog\Logger::class));
    $scrapper = new \Spieldose\Scrapper($dbh);
    $results = $scrapper->getAllDatabaseFiles();
    $totalResults = count($results);
    if ($totalResults > 0) {
        echo "Checking filesystem existence & removing orphaned data..." . PHP_EOL;
        $totalDeleted = 0;
        $dbh->beginTransaction();
        $errors = false;
        try {
            for ($i = 0; $i < $totalResults; $i++) {
                $path = $results[$i]->path . DIRECTORY_SEPARATOR . $results[$i]->filename;
                $logger->debug(sprintf("Checking path %d/%d", $i + 1, $totalResults), array($path));
                if (! file_exists($path)) {
                    $logger->notice("Path not found");
                    $scrapper->removeDatabaseReferences($results[$i]->id);
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
        if (! $errors) {
            $dbh->commit();
        } else {
            $dbh->rollBack();
        }
    } else {
        echo "No data (database empty)" . PHP_EOL;
    }

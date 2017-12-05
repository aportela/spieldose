<?php

    declare(strict_types=1);

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    echo "Spieldose database cleaner" . PHP_EOL;

    $app = (new \Spieldose\App())->get();

    $missingExtensions = array_diff($app->getContainer()["settings"]["phpRequiredExtensions"], get_loaded_extensions());
    if (count($missingExtensions) > 0) {
        echo "Error: missing php extension/s: " . implode(", ", $missingExtensions) . PHP_EOL;
        exit;
    }

    $c = $app->getContainer();
    $c["scrapLogger"]->info("Database cleaner started");
    $dbh = new \Spieldose\Database\DB($c);
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
                if (! file_exists($path)) {
                    $c["scrapLogger"]->debug("Database file not found on filesystem", array($path));
                    $scrapper->removeDatabaseReferences($results[$i]->id);
                    $totalDeleted++;
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
            $c["scrapLogger"]->debug("Error cleaning database", array($e->getMessage()));
        }
        if (! $errors) {
            $dbh->commit();
        } else {
            $dbh->rollBack();
        }
    } else {
        echo "No data (database empty)" . PHP_EOL;
    }


    /*
    $cmdLine = new \Spieldose\CmdLine("", array("path:"));
    if ($cmdLine->hasParam("path")) {
        $musicPath = $cmdLine->getParamValue("path");
        if (file_exists($musicPath)) {
            $c = $app->getContainer();
            $c["scanLogger"]->info("Scanner started");
            $dbh = new \Spieldose\Database\DB($c);
            if ((new \Spieldose\Database\Version($dbh, $c->get("settings")['database']['type']))->hasUpgradeAvailable()) {
                $c["scanLogger"]->warning("Process stopped: upgrade database before continue");
                echo "New database version available, an upgrade is required before continue." . PHP_EOL;
                exit;
            }
            $files = \Spieldose\FileSystem::getRecursiveDirectoryFiles($musicPath);
            $totalFiles = count($files);
            echo sprintf("Reading %d files from path: %s%s", $totalFiles, $musicPath, PHP_EOL);
            $scrapper = new \Spieldose\Scrapper($dbh);
            $failed = array();
            for ($i = 0; $i < $totalFiles; $i++) {
                try {
                    $c["scanLogger"]->debug("Processing " . $files[$i]);
                    $scrapper->scrapFileTags($files[$i]);
                } catch (\Throwable $e) {
                    $c["scanLogger"]->error("Error: " . $e->getMessage(), array('file' => __FILE__, 'line' => __LINE__));
                    $failed[] = $files[$i];
                }
                \Spieldose\Utils::showProgressBar($i + 1, $totalFiles, 20);
            }
            $totalFailed = count($failed);
            if ($totalFailed > 0) {
                echo sprintf("Failed to scrap %d files:%s", $totalFailed, PHP_EOL);
                foreach($failed as $f) {
                    echo " " . $f . PHP_EOL;
                }
            }
            $c["scanLogger"]->info("Scanner finished");
        } else {
            echo "Invalid music path" . PHP_EOL;
        }
    } else {
        echo "No required params found: --path <music_path>" . PHP_EOL;
    }
    */

?>
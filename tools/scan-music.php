<?php

    declare(strict_types=1);

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    echo "Spieldose music scanner" . PHP_EOL;

    $app = (new \Spieldose\App())->get();

    $missingExtensions = array_diff($app->getContainer()["settings"]["phpRequiredExtensions"], get_loaded_extensions());
    if (count($missingExtensions) > 0) {
        echo "Error: missing php extension/s: " . implode(", ", $missingExtensions) . PHP_EOL;
        exit;
    }

    $cmdLine = new \Spieldose\CmdLine("", array("path:"));
    if ($cmdLine->hasParam("path")) {
        $musicPath = $cmdLine->getParamValue("path");
        if (file_exists($musicPath)) {
            $c = $app->getContainer();
            $c["scanLogger"]->info("Scanner started");
            $files = \Spieldose\FileSystem::getRecursiveDirectoryFiles($musicPath);
            $totalFiles = count($files);
            echo sprintf("Reading %d files from path: %s%s", $totalFiles, $musicPath, PHP_EOL);
            $dbh = new \Spieldose\Database\DB();
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

?>
<?php

    declare(strict_types=1);

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    echo "Spieldose scan utility " . PHP_EOL;

    $cmdLine = new \Spieldose\CmdLine("", array("musicPath:"));
    $musicPath = $cmdLine->getParamValue("musicPath");
    if ($musicPath != null) {
        if (file_exists($musicPath)) {
            $files = \Spieldose\FileSystem::getRecursiveDirectoryFiles($musicPath);
            $totalFiles = count($files);
            echo sprintf("Reading %d files from path: %s%s", $totalFiles, $musicPath, PHP_EOL);
            $dbh = new \Spieldose\Database\DB();
            $scrapper = new \Spieldose\Scrapper();
            $failed = array();
            for ($i = 0; $i < $totalFiles; $i++) {
                try {
                    $scrapper->scrapFileTags($dbh, $files[$i]);
                } catch (\Throwable $e) {
                    $failed[] = $files[$i];
                }
                \Spieldose\Utils::showProgressBar($i + 1, $totalFiles, 20);
            }
            $totalFailed = count($failed);
            if ($totalFailed > 0) {
                echo sprintf("Failed to scrap %d files:%s", $totalFailed, PHP_EOL);
                print_r($failed);
            }
        } else {
            echo sprintf("Directory not found: %s%s", $musicPath, PHP_EOL);
        }
    } else {
        echo sprintf("Invalid params, use %s --musicPath path%s", $argv[0], PHP_EOL);
    }

?>
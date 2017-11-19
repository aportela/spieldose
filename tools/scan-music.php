<?php

    declare(strict_types=1);

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    echo "Spieldose music scanner" . PHP_EOL;

    $settings = require dirname(__DIR__) . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "AppSettings.php";

    $app = (new \Spieldose\App($settings))->get();

    $container = $app->getContainer();

    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['scanLogger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['path'], 0, $settings['level']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $logger->pushHandler($handler);
        return ($logger);
    };

    $cmdLine = new \Spieldose\CmdLine("", array("path:"));
    if ($cmdLine->hasParam("path")) {
        $musicPath = $cmdLine->getParamValue("path");
        if (file_exists($musicPath)) {
            $c = $app->getContainer();
            $c["logger"]->info("Scanner started");
            $files = \Spieldose\FileSystem::getRecursiveDirectoryFiles($musicPath);
            $totalFiles = count($files);
            echo sprintf("Reading %d files from path: %s%s", $totalFiles, $musicPath, PHP_EOL);
            $dbh = new \Spieldose\Database\DB();
            $scrapper = new \Spieldose\Scrapper($dbh);
            $failed = array();
            for ($i = 0; $i < $totalFiles; $i++) {
                try {
                    $c["logger"]->debug("Processing " . $files[$i]);
                    $scrapper->scrapFileTags($files[$i]);
                } catch (\Throwable $e) {
                    $c["logger"]->error("Error: " . $e->getMessage(), array('file' => __FILE__, 'line' => __LINE__));
                    $failed[] = $files[$i];
                }
                \Spieldose\Utils::showProgressBar($i + 1, $totalFiles, 20);
            }
            $totalFailed = count($failed);
            if ($totalFailed > 0) {
                echo sprintf("Failed to scrap %d files:%s", $totalFailed, PHP_EOL);
            }
            $c["logger"]->info("Scanner finished");
        } else {
            echo "Invalid music path" . PHP_EOL;
        }
    } else {
        echo "No required params found: --path <music_path>" . PHP_EOL;
    }

?>
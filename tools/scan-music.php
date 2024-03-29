<?php


    declare(strict_types=1);

    use DI\ContainerBuilder;
    use Slim\App;

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";


    $containerBuilder = new ContainerBuilder();

    // Set up settings

    $containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

    // Build PHP-DI Container instance
    $container = $containerBuilder->build();

    // Create App instance
    $app = $container->get(App::class);
    echo "Spieldose music scanner" . PHP_EOL;

    $settings = $container->get('settings');

    $missingExtensions = array_diff($settings["phpRequiredExtensions"], get_loaded_extensions());
    if (count($missingExtensions) > 0) {
        echo "Error: missing php extension/s: " . implode(", ", $missingExtensions) . PHP_EOL;
        exit;
    }

    $cmdLine = new \Spieldose\CmdLine("", array("path:"));
    if ($cmdLine->hasParam("path")) {
        $musicPath = $cmdLine->getParamValue("path");
        if (file_exists($musicPath)) {
            //$c = $app->getContainer();
            //$c["scanLogger"]->info("Scanner started");
            $dbh = new \Spieldose\Database\DB(
                $container->get(PDO::class), $container->get(\Monolog\Logger::class)
            );
            if ((new \Spieldose\Database\Version($dbh, "PDO_SQLITE"))->hasUpgradeAvailable()) {
                //$c["scanLogger"]->warning("Process stopped: upgrade database before continue");
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
                    //$c["scanLogger"]->debug("Processing " . $files[$i]);
                    $scrapper->scrapFileTags($files[$i]);
                } catch (\Throwable $e) {
                    //$c["scanLogger"]->error("Error: " . $e->getMessage(), array('file' => __FILE__, 'line' => __LINE__));
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
            //$c["scanLogger"]->info("Scanner finished");
            $failed = array();
            $directories = \Spieldose\FileSystem::getRecursiveDirectories($musicPath);
            $totalDirectories = count($directories);
            echo sprintf("Scanning %d directories from path: %s%s", $totalDirectories, $musicPath, PHP_EOL);
            for ($i = 0; $i < $totalDirectories; $i++) {
                try {
                    //$c["scanLogger"]->debug("Processing " . $directories[$i]);
                    foreach(glob($directories[$i] . DIRECTORY_SEPARATOR . $settings["albumCoverPathValidFilenames"], GLOB_BRACE) as $file) {
                        //echo dirname($file) .PHP_EOL;
                        \Spieldose\Album::saveLocalAlbumCover($dbh, dirname($file), basename($file));
                    }
                } catch (\Throwable $e) {
                    //$c["scanLogger"]->error("Error: " . $e->getMessage(), array('file' => __FILE__, 'line' => __LINE__));
                    $failed[] = $directories[$i];
                }
                \Spieldose\Utils::showProgressBar($i + 1, $totalDirectories, 20);
            }
        } else {
            echo "Invalid music path" . PHP_EOL;
        }
    } else {
        echo "No required params found: --path <music_path>" . PHP_EOL;
    }

?>
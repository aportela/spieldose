<?php

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

echo "Spieldose scanner" . PHP_EOL;

$logger = $container->get(\Monolog\Logger::class);

$logger->debug("Scan started");

//$app = (new \Spieldose\App())->get();

$settings = $container->get('settings');

$missingExtensions = array_diff($settings["phpRequiredExtensions"], get_loaded_extensions());
if (count($missingExtensions) > 0) {
    $logger->critical("Error: missing php extension/s: ", [implode(", ", $missingExtensions)]);
    echo "Error: missing php extension/s: " . implode(", ", $missingExtensions) . PHP_EOL;
    exit;
} else {
    $cmdLine = new \Spieldose\CmdLine("", array("path:"));
    if ($cmdLine->hasParam("path")) {
        $musicPath = $cmdLine->getParamValue("path");
        if (file_exists($musicPath)) {
            $logger->debug("Scanning base path: " . $musicPath);
            $db = $container->get(DB::class);

            $files = \Spieldose\FileSystem::getRecursiveDirectoryFiles($musicPath);
            $totalFiles = count($files);
            echo sprintf("Reading %d files from path: %s%s", $totalFiles, $musicPath, PHP_EOL);
            //$scrapper = new \Spieldose\Scrapper($dbh);
            $failed = array();
            for ($i = 0; $i < $totalFiles; $i++) {
                try {
                    $scanner = new \Spieldose\Scanner($db, $logger);
                    $scanner->scan(($files[$i]));
                    //$scrapper->scrapFileTags($files[$i]);
                } catch (\Throwable $e) {
                    //$c["scanLogger"]->error("Error: " . $e->getMessage(), array('file' => __FILE__, 'line' => __LINE__));
                    //$failed[] = $files[$i];
                    $logger->critical($e->getMessage());
                    throw $e;
                }
                //\Spieldose\Utils::showProgressBar($i + 1, $totalFiles, 20);
            }
            $totalFailed = count($failed);
            if ($totalFailed > 0) {
                echo sprintf("Failed to scrap %d files:%s", $totalFailed, PHP_EOL);
                foreach ($failed as $f) {
                    echo " " . $f . PHP_EOL;
                }
            }
            $scanner->cleanUp();
        } else {
            echo "Invalid music path" . PHP_EOL;
        }
    } else {
        echo "No required params found: --path <music_path>" . PHP_EOL;
    }
}

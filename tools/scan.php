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
        $scanner = new \Spieldose\Scanner($db, $logger);
        $cmdLine = new \Spieldose\CmdLine("", array("path:", "cleanup"));
        if ($cmdLine->hasParam("path")) {
            $musicPath = realpath($cmdLine->getParamValue("path"));
            if (file_exists($musicPath)) {
                $logger->info("Scanning base path: " . $musicPath);
                $files = \Spieldose\FileSystem::getRecursiveDirectoryFiles($musicPath);
                $totalFiles = count($files);
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
            }
        }
        if ($cmdLine->hasParam(("cleanup"))) {
            $logger->debug("Cleanup started");
            $scanner->cleanUp();
        }
    } catch (\Exception $e) {
        echo "Uncaught exception: " . $e->getMessage() . PHP_EOL;
        $logger->critical("Uncaught exception: " . $e->getMessage());
    }
}

<?php

    declare(strict_types=1);

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    echo "Spieldose thumbnail cache generator" . PHP_EOL;

    $app = (new \Spieldose\App())->get();

    $c = $app->getContainer();

    $missingExtensions = array_diff($c["settings"]["phpRequiredExtensions"], get_loaded_extensions());
    if (count($missingExtensions) > 0) {
        echo "Error: missing php extension/s: " . implode(", ", $missingExtensions) . PHP_EOL;
        exit;
    }

    $actualVersion = 0;

    $dbh = new \Spieldose\Database\DB($c, $container->get(\Monolog\Logger::class));
    $thumbnailUrls = \Spieldose\Thumbnail::getThumbnailUrls($dbh);
    $totalThumbnails = count($thumbnailUrls);
    if ($totalThumbnails > 0) {
        echo sprintf("Processing %d thumbnails%s", $totalThumbnails, PHP_EOL);
        $c["thumbnailLogger"]->info("Thumbnail cache regeneration started");
        for ($i = 0; $i < $totalThumbnails; $i++) {
            try {
                $c["thumbnailLogger"]->debug("Processing " . $thumbnailUrls[$i]);
                \Spieldose\Thumbnail::getCachedLocalPathFromUrl($thumbnailUrls[$i]);
            } catch (\Throwable $e) {
                $c["thumbnailLogger"]->error("Error: " . $e->getMessage(), array('file' => __FILE__, 'line' => __LINE__));
                $failed[] = $thumbnailUrls[$i];
            }
            \Spieldose\Utils::showProgressBar($i + 1, $totalThumbnails, 20);
            usleep(1000);
        }
        $c["thumbnailLogger"]->info("Thumbnail cache regeneration finished");
    } else {
        echo "No thumbnails found" . PHP_EOL;
    }

?>
<?php

    namespace Spieldose;

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR. "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    echo "Spieldose scan utility " . PHP_EOL;

    $cmdLine = new \Spieldose\CmdLine();
    if ($cmdLine->hasMusicPath()) {
        $musicPath = $cmdLine->getMusicPath();
        if (file_exists($musicPath)) {
            $files = \Spieldose\FileSystem::getRecursiveDirectoryFiles($musicPath);
            $totalFiles = count($files);
            echo sprintf("Reading %d files from path: %s%s", $totalFiles, $musicPath, PHP_EOL);
            for ($i = 0; $i < $totalFiles; $i++) {
                $id3 = new \Spieldose\ID3();
                $id3->analyze($files[$i]);
                \Spieldose\Utils::showProgressBar($i + 1, $totalFiles, 20);
            }
        } else {
            echo sprintf("Directory not found: %s%s", $musicPath, PHP_EOL);
        }
    } else {
        echo sprintf("Invalid params, use %s -m path or %s --musicPath path%s", $argv[0], $argv[0], PHP_EOL);
    }

?>
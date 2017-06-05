<?php

    namespace Spieldose;

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR. "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    echo "Spieldose scan utility " . PHP_EOL;

    $cmdLine = new \Spieldose\CmdLine("", array("musicPath:"));
    $musicPath = $cmdLine->getParamValue("musicPath");
    if ($musicPath != null) {
        if (file_exists($musicPath)) {
            $files = \Spieldose\FileSystem::getRecursiveDirectoryFiles($musicPath);
            $totalFiles = count($files);
            echo sprintf("Reading %d files from path: %s%s", $totalFiles, $musicPath, PHP_EOL);
            $dbh = new \Spieldose\Database();
            for ($i = 0; $i < $totalFiles; $i++) {
                $id3 = new \Spieldose\ID3();
                $id3->analyze($files[$i]);
                $params = array();
                $fileId = sha1($files[$i]);
                $params[] = (new \Spieldose\DatabaseParam())->str(":id", $fileId);
                $params[] = (new \Spieldose\DatabaseParam())->str(":path", $files[$i]);
                $trackTitle = $id3->getTrackTitle();
                if (! empty($trackTitle)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":title", $trackTitle);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":title");
                }
                $trackArtist = $id3->getTrackArtistName();
                if (! empty($trackArtist)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":artist", $trackArtist);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":artist");
                }
                $trackAlbum = $id3->getAlbum();
                if (! empty($trackAlbum)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":album", $trackAlbum);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":album");
                }
                $trackNumber = $id3->getTrackNumber();
                if (! empty($trackNumber)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":tracknumber", $trackNumber);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":tracknumber");
                }
                $discNumber = $id3->getDiscNumber();
                if (! empty($discNumber)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":discnumber", $discNumber);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":discnumber");
                }
                $albumArtist = $id3->getAlbumArtistName();
                if (! empty($albumArtist)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":albumartist", $albumArtist);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":albumartist");
                }
                $year = $id3->getYear();
                if (! empty($year)) {
                    $params[] = (new \Spieldose\DatabaseParam())->int(":year", intval($year));
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":year");
                }
                $genre = $id3->getGenre();
                if (! empty($genre)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":genre", $genre);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":genre");
                }
                $dbh->execute("REPLACE INTO FILE (id, path, title, artist, album, albumartist, discnumber, tracknumber, year, genre, coverurl) VALUES(:id, :path, :title, :artist, :album, :albumartist, :discnumber, :tracknumber, :year, :genre, NULL);", $params);
                \Spieldose\Utils::showProgressBar($i + 1, $totalFiles, 20);
            }
        } else {
            echo sprintf("Directory not found: %s%s", $musicPath, PHP_EOL);
        }
    } else {
        echo sprintf("Invalid params, use %s --musicPath path%s", $argv[0], PHP_EOL);
    }

?>
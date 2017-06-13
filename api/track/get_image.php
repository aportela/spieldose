<?php

    namespace Spieldose;

    ob_start();

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR . "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    $album = \Spieldose\MusicBrainz\Album::getFromAlbumAndArtist(
        isset($_GET["album"]) ? $_GET["album"]: "",
        isset($_GET["artist"]) ? $_GET["artist"]: ""
    );
    if (isset($album) && isset($album->image)) {
        $ch = curl_init ($album->image);
        $ext = strtolower(pathinfo($album->image, PATHINFO_EXTENSION));
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        //curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/0.2.153.1 Safari/525.19');
        $raw = curl_exec($ch);
        curl_close ($ch);
        ob_clean();
        switch($ext) {
            case "jpg":
                header('Content-Type: image/jpg');
            break;
            case "png":
                header('Content-Type: image/png');
            break;
        }
        die($raw);
    }
    ob_flush();

?>
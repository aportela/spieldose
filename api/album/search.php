<?php
    namespace Spieldose;

    ob_start();

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR . "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    $response = array();

    $response["albums"] = \Spieldose\Album::search(new \Spieldose\Database());
    $totalAlbums = count($response["albums"]);
    for($i = 0; $i < $totalAlbums; $i++) {
        if (! empty($response["albums"][$i]->images)) {
            $response["albums"][$i]->images = json_decode($response["albums"][$i]->images);
        }
    }

    header("Content-Type: application/json; charset=utf-8");

    echo json_encode($response);

    ob_flush();
?>
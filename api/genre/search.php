<?php

    namespace Spieldose;

    ob_start();

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR . "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    $response = array();

    $response["genres"] = \Spieldose\Genre::search(new \Spieldose\Database(), array(), "");

    $totalGenres = count($response["genres"]);

    for($i = 0; $i < $totalGenres; $i++) {
        $response["genres"][$i]->total = intval($response["genres"][$i]->total);
    }

    header("Content-Type: application/json; charset=utf-8");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    echo json_encode($response);

    ob_flush();

?>
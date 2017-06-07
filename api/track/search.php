<?php

    namespace Spieldose;

    ob_start();

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR . "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    $response = array();

    $data = \Spieldose\Track::search(
        new \Spieldose\Database(),
        isset($_POST["actualPage"]) ? intval($_POST["actualPage"]): 1,
        isset($_POST["resultsPage"]) ? intval($_POST["resultsPage"]): 16,
        array(),
        isset($_POST["orderBy"]) ? $_POST["orderBy"]: ""
    );
    $response["tracks"] = $data->results;
    $response["totalResults"] = $data->totalResults;
    $response["actualPage"] = $data->actualPage;
    $response["resultsPage"] = $data->resultsPage;
    $response["totalPages"] = $data->totalPages;

    $totalTracks = count($response["tracks"]);
    for($i = 0; $i < $totalTracks; $i++) {
        if (! empty($response["tracks"][$i]->images)) {
            $response["tracks"][$i]->images = json_decode($response["tracks"][$i]->images);
        }
    }


    header("Content-Type: application/json; charset=utf-8");

    echo json_encode($response);

    ob_flush();

?>
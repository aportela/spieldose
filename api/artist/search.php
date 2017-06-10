<?php

    namespace Spieldose;

    ob_start();

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR . "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    $response = array();

    $data = \Spieldose\Artist::search(
        new \Spieldose\Database(),
        isset($_POST["actualPage"]) ? intval($_POST["actualPage"]): 1,
        isset($_POST["resultsPage"]) ? intval($_POST["resultsPage"]): 16,
        array(),
        isset($_POST["orderBy"]) ? $_POST["orderBy"]: ""
    );
    $response["artists"] = $data->results;
    $response["totalResults"] = $data->totalResults;
    $response["actualPage"] = $data->actualPage;
    $response["resultsPage"] = $data->resultsPage;
    $response["totalPages"] = $data->totalPages;

    header("Content-Type: application/json; charset=utf-8");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    echo json_encode($response);

    ob_flush();

?>
<?php

    namespace Spieldose;

    ob_start();

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR . "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    $response = array("success" => true);

    \Spieldose\User::logout();

    header("Content-Type: application/json; charset=utf-8");

    echo json_encode($response);

    ob_flush();

?>
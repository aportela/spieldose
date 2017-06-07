<?php

    namespace Spieldose;

    ob_start();

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR . "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    $response = array();

    $dbh = new \Spieldose\Database();
    $data = new \Spieldose\Artist(isset($_GET["name"]) ? $_GET["name"]: "");
    try {
        $data->get(new \Spieldose\Database());
        $response["name"] = $data->name;
        $response["albums"] = $data->albums;
        http_response_code(200);
    } catch (\Spieldose\Exception\InvalidParamsException $e) {
        http_response_code(400);
        $response["missingParams"] = array($e->getMessage());
    } catch (\Spieldose\Exception\NotFoundException $e) {
        http_response_code(404);
        if (DEBUG) {
            $response["exceptionMessage"] = $e->getMessage();
        }
    } catch (\Throwable $e) {
        http_response_code(500);
        if (DEBUG) {
            $response["exceptionMessage"] = $e->getMessage();
        }
    }

    header("Content-Type: application/json; charset=utf-8");

    echo json_encode($response);

    ob_flush();

?>
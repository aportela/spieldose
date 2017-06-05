<?php

    namespace Spieldose;

    ob_start();

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR . "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    $response = array("success" => false);

    try {
        $response["success"] = (new \Spieldose\User(isset($_POST["login"]) ? $_POST["login"]: ""))->login(new \Spieldose\Database(), isset($_POST["password"]) ? $_POST["password"]: "");
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
    } finally {
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($response);
    }

    ob_flush();

?>
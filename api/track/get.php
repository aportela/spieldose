<?php

    namespace Spieldose;

    ob_start();

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR . "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    $response = array();

    $dbh = new \Spieldose\Database();
    $track = new \Spieldose\Track(isset($_GET["id"]) ? $_GET["id"]: "");
    $errors = false;
    try {
        $track->get(new \Spieldose\Database());
        if (file_exists($track->path)) {
            $filesize = filesize($track->path);
            $offset = 0;
            $length = $filesize;
            // https://stackoverflow.com/a/157447
            if (isset($_SERVER['HTTP_RANGE'])) {
                // if the HTTP_RANGE header is set we're dealing with partial content
                $partialContent = true;
                // find the requested range
                // this might be too simplistic, apparently the client can request
                // multiple ranges, which can become pretty complex, so ignore it for now
                preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
                $offset = intval($matches[1]);
                $length = ((isset($matches[2])) ? intval($matches[2]) : $filesize) - $offset;
            } else {
                $partialContent = false;
            }
            $file = fopen($track->path, 'r');
            fseek($file, $offset);
            $data = fread($file, $length);
            fclose($file);
            if ($partialContent) {
                // output the right headers for partial content
                header('HTTP/1.1 206 Partial Content');
                header('Content-Range: bytes ' . $offset . '-' . ($offset + $length - 1) . '/' . $filesize);
            }
            http_response_code(200);
            header('Content-Type: ' . "audio/mpeg");
            header('Content-Disposition: attachment; filename="' . basename($track->path) . '"');
            header('Content-Length: ' . $filesize);
            header('Accept-Ranges: bytes');
            print($data);
        } else {
            http_response_code(404);
            if (DEBUG) {
                $response["exceptionMessage"] = $track->path;
            }
        }

    } catch (\Spieldose\Exception\InvalidParamsException $e) {
        $errors = true;
        http_response_code(400);
        $response["missingParams"] = array($e->getMessage());
    } catch (\Spieldose\Exception\NotFoundException $e) {
        $errors = true;
        http_response_code(404);
        if (DEBUG) {
            $response["exceptionMessage"] = $e->getMessage();
        }
    } catch (\Throwable $e) {
        $errors = true;
        http_response_code(500);
        if (DEBUG) {
            $response["exceptionMessage"] = $e->getMessage();
        }
    } finally {
        if ($errors) {
            header("Content-Type: application/json; charset=utf-8");
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            echo json_encode($response);
        }
    }

    ob_flush();

?>
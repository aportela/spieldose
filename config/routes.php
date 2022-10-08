<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

return function (App $app) {
    $app->get('/', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
        /*
        return $this->get('Twig')->render($response, 'test.twig', [
            'name' => "fpp"
        ]);
        */
        //$this->logger->info($request->getOriginalMethod() . " " . $request->getUri()->getPath());
        //$v = new \Spieldose\Database\Version(new \Spieldose\Database\DB($this->get(PDO::class), $this->get(\Monolog\Logger::class)), $this->get('settings')['database']['type']);

        $settings = $this->get('settings');
        return $this->get('Twig')->render($response, 'index.html.twig', [
            //'settings' => $this->get('settings'),
            //'settings' => $settings["twigParams"],
            "locale" => $settings['common']['locale'],
            "webpack" => $settings['webpack'],
            'initialState' => json_encode(
                array(
                    "logged" => \Spieldose\User::isLogged(),
                    "sessionExpireMinutes" => session_cache_expire(),
                    //'upgradeAvailable' => $v->hasUpgradeAvailable(),
                    "defaultResultsPage" => $settings['common']['defaultResultsPage'],
                    "allowSignUp" => $settings['common']['allowSignUp'],
                    "liveSearch" => $settings['common']['liveSearch'],
                    "locale" => $settings['common']['locale']
                )
            )
        ]);
    });

    $app->group("/api2", function (Slim\Routing\RouteCollectorProxy $group) {
        $group->get('/track/search', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
            $db = $this->get(DB::class);
            $payload = array(
                "tracks" => \Spieldose\Track::searchNew($db)
            );
            $response->getBody()->write(json_encode($payload));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });

        $group->get('/file/{id}', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
            $track  = new \Spieldose\Track($args["id"]);
            $db = $this->get(DB::class);
            $track->getNew($db);
            if (file_exists($track->path)) {
                //$track->incPlayCount($db);
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
                $response->getBody()->write($data);
                if ($partialContent) {
                    // output the right headers for partial content
                    return $response->withStatus(206)
                        ->withHeader('Content-Type', $track->mime ? $track->mime : "application/octet-stream")
                        ->withHeader('Content-Disposition', 'attachment; filename="' . basename($track->path) . '"')
                        ->withHeader('Content-Length', $filesize)
                        ->withHeader('Content-Range', 'bytes ' . $offset . '-' . ($offset + $length - 1) . '/' . $filesize)
                        ->withHeader('Accept-Ranges', 'bytes');
                } else {
                    return $response->withStatus(200)
                        ->withHeader('Content-Type', $track->mime ? $track->mime : "application/octet-stream")
                        ->withHeader('Content-Disposition', 'attachment; filename="' . basename($track->path) . '"')
                        ->withHeader('Content-Length', $filesize)
                        ->withHeader('Accept-Ranges', 'bytes');
                }
            } else {
                throw new \Spieldose\Exception\NotFoundException("id");
            }
        });

        $group->get('/track/thumbnail/{width}/{height}/{id}', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
            $db = $this->get(DB::class);
            $localPath = \Spieldose\Track::getLocalThumbnail($db, $args["id"], intval($args["width"]), intval($args["height"]));
            if (!empty($localPath) && file_exists(($localPath))) {
                $filesize = filesize($localPath);
                $f = fopen($localPath, 'r');
                fseek($f, 0);
                $data = fread($f, $filesize);
                fclose($f);
                $response->getBody()->write($data);
                return $response
                    ->withHeader('Content-Type', "image/jpeg")
                    ->withHeader('Content-Length', $filesize)
                    ->withStatus(200);
            } else {
                throw new \Exception("Invalid / empty path: " . $localPath);
            }
        });

    })->add(\Spieldose\Middleware\APIExceptionCatcher::class);
};

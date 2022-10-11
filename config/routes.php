<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;

return function (App $app) {
    $app->get('/', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
        $logger = $this->get(\Spieldose\Logger\HTTPRequestLogger::class);
        $logger->info($request->getMethod() . " " . $request->getUri()->getPath());
        $settings = $this->get('settings');
        return $this->get('Twig')->render($response, 'index.html.twig', [
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
    })->add(\Spieldose\Middleware\JWT::class);

    $app->group(
        "/api2",
        function (RouteCollectorProxy $group) {
            $group->get('/track/search', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
                $logger = $this->get(\Spieldose\Logger\HTTPRequestLogger::class);
                $logger->info($request->getMethod() . " " . $request->getUri()->getPath());
                $params = $request->getQueryParams();
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                $payload = array(
                    "tracks" => \Spieldose\Track::searchNew($db, $params['q'], $params['artist'], $params['albumArtist'], $params['album'])
                );
                $response->getBody()->write(json_encode($payload));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->get('/file/{id}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
                $logger = $this->get(\Spieldose\Logger\HTTPRequestLogger::class);
                $logger->info($request->getMethod() . " " . $request->getUri()->getPath());
                if (!empty($args["id"])) {
                    $file = new \Spieldose\File($this, $args["id"]);
                    $file->get();
                    if (file_exists($file->path)) {
                        //$track->incPlayCount($db);
                        $length = $file->length;
                        // https://stackoverflow.com/a/157447
                        $partialContent = false;
                        $offset = 0;
                        if (isset($_SERVER['HTTP_RANGE'])) {
                            // if the HTTP_RANGE header is set we're dealing with partial content
                            $partialContent = true;
                            // find the requested range
                            // this might be too simplistic, apparently the client can request
                            // multiple ranges, which can become pretty complex, so ignore it for now
                            preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
                            $offset = intval($matches[1]);
                            $length = ((isset($matches[2])) ? intval($matches[2]) : $file->length) - $offset;
                        }
                        $response->getBody()->write($file->getData($offset, $length));
                        if ($partialContent) {
                            // output the right headers for partial content
                            return $response->withStatus(206)
                                ->withHeader('Content-Type', $file->mime ? $file->mime : "application/octet-stream")
                                ->withHeader('Content-Disposition', 'attachment; filename="' . basename($file->path) . '"')
                                ->withHeader('Content-Length', $file->length)
                                ->withHeader('Content-Range', 'bytes ' . $offset . '-' . ($offset + $length - 1) . '/' . $file->length)
                                ->withHeader('Accept-Ranges', 'bytes');
                        } else {
                            return $response->withStatus(200)
                                ->withHeader('Content-Type', $file->mime ? $file->mime : "application/octet-stream")
                                ->withHeader('Content-Disposition', 'attachment; filename="' . basename($file->path) . '"')
                                ->withHeader('Content-Length', $file->length)
                                ->withHeader('Accept-Ranges', 'bytes');
                        }
                    } else {
                        throw new \Spieldose\Exception\NotFoundException("id");
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("id");
                }
            });

            $group->get('/track/thumbnail/{width}/{height}/{id}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
                $logger = $this->get(\Spieldose\Logger\HTTPRequestLogger::class);
                //$logger->info($request->getMethod() . " " . $request->getUri()->getPath());
                //$cachedETAG = $request->getHeaderLine('HTTP_IF_NONE_MATCH');
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                $logger = $this->get(\Spieldose\Logger\ThumbnailLogger::class);
                try {
                    $localPath = \Spieldose\Track::getLocalThumbnail($db, $logger, $args["id"], intval($args["width"]), intval($args["height"]));
                } catch (\Spieldose\Exception\NotFoundException $e) {
                }
                if (empty($localPath)) {
                    try {
                        $localPath = \Spieldose\Track::getRemoteThumbnail($db, $logger, $args["id"], intval($args["width"]), intval($args["height"]));
                    } catch (\Spieldose\Exception\NotFoundException $e) {
                    }
                }
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
                        ->withHeader('ETag', sha1($args["id"] . $localPath . $filesize))
                        ->withHeader('Cache-Control', 'max-age=86400')
                        ->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\NotFoundException("Invalid / empty path for id: " . $args["id"]);
                }
            });
        }
    )->add(\Spieldose\Middleware\APIExceptionCatcher::class)
        ->add(\Spieldose\Middleware\JWT::class);
};

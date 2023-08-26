<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response, array $args) {
        return $this->get('Twig')->render($response, 'index-quasar.html.twig', []);
    })->add(\Spieldose\Middleware\JWT::class);

    $app->group(
        '/api/2',
        function (RouteCollectorProxy $group) {
            $group->get('/initial_state', function (Request $request, Response $response, array $args) {
                $payload = json_encode(
                    [
                        'initialState' => json_encode(\Spieldose\Utils::getInitialState($this))
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->post('/user/sign-up', function (Request $request, Response $response, array $args) {
                $settings = $this->get('settings');
                if ($settings['common']['allowSignUp']) {
                    $params = $request->getParsedBody();
                    $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                    if (\Spieldose\User::isEmailUsed($db, $params["email"] ?? "")) {
                        throw new \Spieldose\Exception\AlreadyExistsException("email");
                    } else {
                        $user = new \Spieldose\User(
                            $params["id"] ?? "",
                            $params["email"] ?? "",
                            $params["password"] ?? ""
                        );
                        $user->add($db);
                        $payload = json_encode([]);
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                    }
                } else {
                    throw new \Spieldose\Exception\AccessDeniedException("");
                }
            });

            $group->post('/user/sign-in', function (Request $request, Response $response, array $args) {
                $settings = $this->get('settings');
                $params = $request->getParsedBody();
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                $user = new \Spieldose\User(
                    "",
                    $params["email"] ?? "",
                    $params["password"] ?? ""
                );
                $user->signIn($db);
                $payload = json_encode([]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->post('/user/sign-out', function (Request $request, Response $response, array $args) {
                $settings = $this->get('settings');
                \Spieldose\User::signOut();
                $payload = json_encode([]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->post('/track/search', function (Request $request, Response $response, array $args) {
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                $params = $request->getParsedBody();
                $filter = array(
                    "text" => $params["filter"]["text"] ?? "",
                    "path" => $params["filter"]["path"] ?? "",
                );
                $params = $request->getParsedBody();
                $tracks = \Spieldose\Entities\Track::search($db, 1, 32, $filter);
                $payload = json_encode(["tracks" => $tracks]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->get('/thumbnail/{size}/remote/{entity}/', function (Request $request, Response $response, array $args) {
                $queryParams = $request->getQueryParams();
                if (isset($queryParams["url"]) && !empty($queryParams["url"]) && filter_var($queryParams["url"], FILTER_VALIDATE_URL)) {
                    if (!in_array($args['size'], ['small', 'normal'])) {
                        throw new \Spieldose\Exception\InvalidParamsException('size');
                    }
                    if (!in_array($args['entity'], ['artist', 'album'])) {
                        throw new \Spieldose\Exception\InvalidParamsException('entity');
                    }
                    $settings = null;
                    switch ($args['entity']) {
                        case 'artist':
                            $settings = $this->get('settings')['thumbnails']['artists'];
                            break;
                        case 'album':
                            $settings = $this->get('settings')['thumbnails']['albums'];
                            break;
                    }
                    //$cachedETAG = $request->getHeaderLine('HTTP_IF_NONE_MATCH');
                    $logger = $this->get(\Spieldose\Logger\ThumbnailLogger::class);
                    $localPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "thumbnails";
                    $thumbnail = new \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail($logger, $settings['basePath']);
                    $thumbnail->setDimensions($settings['sizes'][$args['size']]['width'], $settings['sizes'][$args['size']]['height']);
                    $thumbnail->setQuality($settings['sizes'][$args['size']]['quality']);
                    if ($thumbnail->getFromRemoteURL($queryParams["url"]) && !empty($thumbnail->path) && file_exists(($thumbnail->path))) {
                        $filesize = filesize($thumbnail->path);
                        $f = fopen($thumbnail->path, 'r');
                        fseek($f, 0);
                        $data = fread($f, $filesize);
                        fclose($f);
                        $response->getBody()->write($data);
                        return $response
                            ->withHeader('Content-Type', 'image/jpeg')
                            ->withHeader('Content-Length', $filesize)
                            ->withHeader('ETag', sha1($queryParams["url"] . $thumbnail->path . $filesize))
                            ->withHeader('Cache-Control', 'max-age=86400')
                            ->withStatus(200);
                    } else {
                        throw new \Spieldose\Exception\NotFoundException('Invalid / empty path for url: ' . $queryParams["url"]);
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('Invalid / empty url param');
                }
            });

            $group->get('/track/thumbnail/{size}/{id}', function (Request $request, Response $response, array $args) {
                if (!in_array($args['size'], ['small', 'normal'])) {
                    throw new \Spieldose\Exception\InvalidParamsException('size');
                }
                $settings = $this->get('settings')['thumbnails'];
                //$cachedETAG = $request->getHeaderLine('HTTP_IF_NONE_MATCH');
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                $logger = $this->get(\Spieldose\Logger\ThumbnailLogger::class);
                $localPathNormalSize = null;
                try {
                    $localPathNormalSize = \Spieldose\Track::getLocalThumbnail($db, $logger, $args['id'], $settings['sizes']['normal']['quality'], $settings['sizes']['normal']['width'], $settings['sizes']['normal']['height']);
                } catch (\Spieldose\Exception\NotFoundException $e) {
                }
                if (empty($localPathNormalSize)) {
                    try {
                        $localPathNormalSize = \Spieldose\Track::getRemoteThumbnail($db, $logger, $args['id'], $settings['sizes']['normal']['quality'], $settings['sizes']['normal']['width'], $settings['sizes']['normal']['height']);
                    } catch (\Spieldose\Exception\NotFoundException $e) {
                    }
                }
                $localPathSmallSize = null;
                try {
                    $localPathSmallSize = \Spieldose\Track::getLocalThumbnail($db, $logger, $args['id'], $settings['sizes']['small']['quality'], $settings['sizes']['small']['width'], $settings['sizes']['small']['height']);
                } catch (\Spieldose\Exception\NotFoundException $e) {
                }
                if (empty($localPathSmallSize)) {
                    try {
                        $localPathSmallSize = \Spieldose\Track::getRemoteThumbnail($db, $logger, $args['id'], $settings['sizes']['small']['quality'], $settings['sizes']['small']['width'], $settings['sizes']['small']['height']);
                    } catch (\Spieldose\Exception\NotFoundException $e) {
                    }
                }
                $localPath = $args['size'] == 'small' ? $localPathSmallSize : $localPathNormalSize;
                if (!empty($localPath) && file_exists(($localPath))) {
                    $filesize = filesize($localPath);
                    $f = fopen($localPath, 'r');
                    fseek($f, 0);
                    $data = fread($f, $filesize);
                    fclose($f);
                    $response->getBody()->write($data);
                    return $response
                        ->withHeader('Content-Type', 'image/jpeg')
                        ->withHeader('Content-Length', $filesize)
                        ->withHeader('ETag', sha1($args['id'] . $localPath . $filesize))
                        ->withHeader('Cache-Control', 'max-age=86400')
                        ->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\NotFoundException('Invalid / empty path for id: ' . $args['id']);
                }
            });

            $group->get('/cache/thumbnail/{size}/{hash}', function (Request $request, Response $response, array $args) {
                if (!in_array($args['size'], ['small', 'normal'])) {
                    throw new \Spieldose\Exception\InvalidParamsException('size');
                }
                $logger = $this->get(\Spieldose\Logger\ThumbnailLogger::class);
                $settings = $this->get('settings')['thumbnails'];
                $localPath = null;
                try {
                    $thumbnail = new \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail($logger, $settings["basePath"]);
                    $thumbnail->setDimensions($settings['sizes'][$args["size"]]['width'], $settings['sizes'][$args["size"]]['height']);
                    $thumbnail->setQuality($settings['sizes'][$args["size"]]['quality']);
                    if ($thumbnail->getFromCache($args["hash"])) {
                        $localPath = $thumbnail->path;
                    }
                } catch (\Spieldose\Exception\NotFoundException $e) {
                }
                if (!empty($localPath) && file_exists(($localPath))) {
                    $filesize = filesize($localPath);
                    $f = fopen($localPath, 'r');
                    fseek($f, 0);
                    $data = fread($f, $filesize);
                    fclose($f);
                    $response->getBody()->write($data);
                    return $response
                        ->withHeader('Content-Type', 'image/jpeg')
                        ->withHeader('Content-Length', $filesize)
                        ->withHeader('ETag', sha1($args["size"] . $args['hash'] . $localPath . $filesize))
                        ->withHeader('Cache-Control', 'max-age=86400')
                        ->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\NotFoundException('Invalid / empty path for hash: ' . $args['hash']);
                }
            });

            $group->post('/artist/search', function (Request $request, Response $response, array $args) {
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                $params = $request->getParsedBody();
                $filter = array(
                    "name" => $params["filter"]["name"] ?? ""
                );
                $params = $request->getParsedBody();
                $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                    [
                        new \aportela\DatabaseBrowserWrapper\SortItem("name", \aportela\DatabaseBrowserWrapper\Order::ASC, true)
                    ]
                );
                $pager = new \aportela\DatabaseBrowserWrapper\Pager(true, $params["pager"]["currentPageIndex"] ?? 1, $params["pager"]["resultsPage"]);
                $data = \Spieldose\Entities\Artist::search($db, $filter, $sort, $pager);
                $payload = json_encode(["data" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->get('/artist/{name}', function (Request $request, Response $response, array $args) {
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                $artist = new \Spieldose\Entities\Artist($db);
                $artist->name = $args["name"];
                $artist->get();
                $payload = json_encode(
                    [
                        'artist' => $artist
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->post('/album/search', function (Request $request, Response $response, array $args) {
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                $params = $request->getParsedBody();
                $filter = array(
                    "title" => $params["filter"]["title"] ?? ""
                );
                $params = $request->getParsedBody();

                $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                    [
                        new \aportela\DatabaseBrowserWrapper\SortItem("title", \aportela\DatabaseBrowserWrapper\Order::ASC, true)
                    ]
                );
                $pager = new \aportela\DatabaseBrowserWrapper\Pager(true, $params["pager"]["currentPageIndex"] ?? 1, $params["pager"]["resultsPage"]);
                $data = \Spieldose\Entities\Album::search($db, $filter, $sort, $pager);
                $payload = json_encode(["data" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->get('/album/small_random_covers/{count:[0-9]+}', function (Request $request, Response $response, array $args) {
                $settings = $this->get('settings')['thumbnails'];
                $coverBasePath = $settings['basePath'] . DIRECTORY_SEPARATOR . $settings['sizes']['small']['quality'] . DIRECTORY_SEPARATOR . $settings['sizes']['small']['width'] . DIRECTORY_SEPARATOR . $settings['sizes']['small']['height'];
                $urls = [];
                if (file_exists($coverBasePath)) {
                    $hashes = array();
                    $rdi = new \RecursiveDirectoryIterator($coverBasePath);
                    foreach (new \RecursiveIteratorIterator($rdi) as $filename => $cur) {
                        $extension = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        if (in_array($extension, ['jpg'])) {
                            $hashes[] = pathinfo($filename)['filename'];
                        }
                    }
                    shuffle($hashes);
                    $count = intval($args["count"]);
                    if (count($hashes) > $count) {
                        $hashes = array_slice($hashes, 0, $count);
                    }
                    $uri = $request->getUri();
                    $urls = array_map(
                        fn ($url) =>
                        sprintf("%s://%s:%d/api/2/cache/thumbnail/%s/%s", $uri->getScheme(), $uri->getHost(), $uri->getPort(), 'small', $url),
                        $hashes
                    );
                }
                $payload = array(
                    'coverURLs' => $urls
                );
                $response->getBody()->write(json_encode($payload));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->get('/file/{id}', function (Request $request, Response $response, array $args) {
                if (!empty($args['id'])) {
                    $file = new \Spieldose\File($this, $args['id']);
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
                                ->withHeader('Content-Type', $file->mime ? $file->mime : 'application/octet-stream')
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
                        throw new \Spieldose\Exception\NotFoundException('id');
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            });

            $group->get('/path/tree', function (Request $request, Response $response, array $args) {
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                $data = \Spieldose\Path::getTree($db);
                $payload = json_encode(["items" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });
        }
    )->add(\Spieldose\Middleware\JWT::class)->add(\Spieldose\Middleware\APIExceptionCatcher::class);
};

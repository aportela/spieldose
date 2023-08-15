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
                    throw new \Spieldose\Exception\NotFoundException('Invalid / empty path for id: ' . $args['id']);
                }
            });

            $group->get('/random_album_covers', function (Request $request, Response $response, array $args) {
                $settings = $this->get('settings')['thumbnails'];
                $lp = $settings['basePath'] . DIRECTORY_SEPARATOR . $settings['sizes']['small']['quality'] . DIRECTORY_SEPARATOR . $settings['sizes']['small']['width'] . DIRECTORY_SEPARATOR . $settings['sizes']['small']['height'];
                $urls = [];
                $hashes = array();
                if (file_exists($lp)) {
                    $rdi = new \RecursiveDirectoryIterator($lp);
                    foreach (new \RecursiveIteratorIterator($rdi) as $filename => $cur) {
                        $extension = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        if (in_array($extension, ['jpg'])) {
                            $hashes[] = pathinfo($filename)['filename'];
                        }
                    }
                    shuffle($hashes);
                    $hashes = array_slice($hashes, 0, 32);
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
        }
    )->add(\Spieldose\Middleware\JWT::class)->add(\Spieldose\Middleware\APIExceptionCatcher::class);
};

<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;

return function (App $app) {
    $app->get('/', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
        $logger = $this->get(\Spieldose\Logger\HTTPRequestLogger::class);
        $logger->info($request->getMethod() . '  ' . $request->getUri()->getPath());
        $settings = $this->get('settings');
        $db = $this->get(\aportela\DatabaseWrapper\DB::class);
        $currentVersion = $db->getCurrentSchemaVersion();
        $lastVersion = $db->getUpgradeSchemaVersion();
        return $this->get('Twig')->render($response, 'index.html.twig', [
            'locale' => $settings['common']['locale'],
            'webpack' => $settings['webpack'],
            'initialState' => json_encode(
                array(
                    'environment' => $settings['environment'],
                    'logged' => \Spieldose\User::isLogged(),
                    'sessionExpireMinutes' => session_cache_expire(),
                    'defaultResultsPage' => $settings['common']['defaultResultsPage'],
                    'allowSignUp' => $settings['common']['allowSignUp'],
                    'liveSearch' => $settings['common']['liveSearch'],
                    'locale' => $settings['common']['locale'],
                    'version' => array(
                        'currentVersion' => $currentVersion,
                        'lastVersion' => $lastVersion,
                        'upgradeAvailable' => $currentVersion < $lastVersion
                    )
                )
            )
        ]);
    });

    $app->group(
        '/api2',
        function (RouteCollectorProxy $group) {

            $group->post('/user/signin', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $u = new \Spieldose\User('', $params['email'] ?? '', $params['password'] ?? '');
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                if ($u->login($db)) {
                    $payload = json_encode(['logged' => true]);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    $payload = json_encode(['logged' => false]);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
                }
            });

            $group->post('/user/signup', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                if ($this->get('settings')['common']['allowSignUp']) {
                    $params = $request->getParsedBody();
                    $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                    $u = new \Spieldose\User(
                        '',
                        $params['email'] ?? '',
                        $params['password'] ?? ''
                    );
                    $exists = false;
                    try {
                        $u->get($db);
                        $exists = true;
                    } catch (\Spieldose\Exception\NotFoundException $e) {
                    }
                    if ($exists) {
                        $payload = json_encode([]);
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
                    } else {
                        $u->id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
                        $u->add($db);
                        $payload = json_encode([]);
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                    }
                } else {
                    throw new \Spieldose\Exception\AccessDeniedException('');
                }
            });

            $group->get('/user/signout', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                \Spieldose\User::logout();
                $payload = json_encode(['logged' => false]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->get('/track/search', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
                $logger = $this->get(\Spieldose\Logger\HTTPRequestLogger::class);
                $logger->info($request->getMethod() . ' ' . $request->getUri()->getPath());
                $params = $request->getQueryParams();
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                $payload = array(
                    'tracks' => \Spieldose\Track::searchNew($db, $params['q'] ?? null, $params['artist'] ?? null, $params['albumArtist'] ?? null, $params['album'] ?? null)
                );
                $response->getBody()->write(json_encode($payload));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->get('/file/{id}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
                $logger = $this->get(\Spieldose\Logger\HTTPRequestLogger::class);
                $logger->info($request->getMethod() . ' ' . $request->getUri()->getPath());
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

            $group->get('/increase_play_count/track/{id}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
                if (!\Spieldose\User::isLogged()) {
                    throw new \Spieldose\Exception\AuthenticationMissingException();
                }
                //$logger = $this->get(\Spieldose\Logger\HTTPRequestLogger::class);
                //$logger->info($request->getMethod() . ' ' . $request->getUri()->getPath());
                if (!empty($args['id'])) {
                    $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                    \Spieldose\Track::incPlayCount($db, $args['id'], \Spieldose\User::getUserId());
                    $payload = json_encode([]);
                    $response->getBody()->write(json_encode($payload));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            });

            $group->get('/love/track/{id}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
                if (!\Spieldose\User::isLogged()) {
                    throw new \Spieldose\Exception\AuthenticationMissingException();
                }
                //$logger = $this->get(\Spieldose\Logger\HTTPRequestLogger::class);
                //$logger->info($request->getMethod() . ' ' . $request->getUri()->getPath());
                if (!empty($args['id'])) {
                    $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                    \Spieldose\Track::love($db, $args['id'], \Spieldose\User::getUserId());
                    $payload = json_encode([]);
                    $response->getBody()->write(json_encode($payload));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            });

            $group->get('/unlove/track/{id}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
                if (!\Spieldose\User::isLogged()) {
                    throw new \Spieldose\Exception\AuthenticationMissingException();
                }
                //$logger = $this->get(\Spieldose\Logger\HTTPRequestLogger::class);
                //$logger->info($request->getMethod() . ' ' . $request->getUri()->getPath());
                if (!empty($args['id'])) {
                    $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                    \Spieldose\Track::unLove($db, $args['id'], \Spieldose\User::getUserId());
                    $payload = json_encode([]);
                    $response->getBody()->write(json_encode($payload));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            });

            $group->get('/track/thumbnail/{size}/{id}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
                if (!in_array($args['size'], ['small', 'normal'])) {
                    throw new \Spieldose\Exception\InvalidParamsException('size');
                }
                $logger = $this->get(\Spieldose\Logger\HTTPRequestLogger::class);
                $settings = $this->get('settings')['thumbnails'];

                //$logger->info($request->getMethod() . " " . $request->getUri()->getPath());
                //$cachedETAG = $request->getHeaderLine('HTTP_IF_NONE_MATCH');
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                $logger = $this->get(\Spieldose\Logger\ThumbnailLogger::class);
                $localPathNormalSize = null;
                try {
                    $localPathNormalSize = \Spieldose\Track::getLocalThumbnail($db, $logger, $args['id'], intval($settings['sizes']['normal']['height']), intval($settings['sizes']['normal']['height']));
                } catch (\Spieldose\Exception\NotFoundException $e) {
                }
                if (empty($localPathNormalSize)) {
                    try {
                        $localPathNormalSize = \Spieldose\Track::getRemoteThumbnail($db, $logger, $args['id'], intval($settings['sizes']['normal']['height']), intval($settings['sizes']['normal']['height']));
                    } catch (\Spieldose\Exception\NotFoundException $e) {
                    }
                }
                $localPathSmallSize = null;
                try {
                    $localPathSmallSize = \Spieldose\Track::getLocalThumbnail($db, $logger, $args['id'], intval($settings['sizes']['small']['height']), intval($settings['sizes']['small']['height']));
                } catch (\Spieldose\Exception\NotFoundException $e) {
                }
                if (empty($localPathSmallSize)) {
                    try {
                        $localPathSmallSize = \Spieldose\Track::getRemoteThumbnail($db, $logger, $args['id'], intval($settings['sizes']['small']['height']), intval($settings['sizes']['small']['height']));
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

            $group->get('/random_album_covers', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
                $settings = $this->get('settings')['thumbnails'];
                $lp = $settings['path'] . DIRECTORY_SEPARATOR . $settings['sizes']['small']['width'] . "x" . $settings['sizes']['small']['height'];
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
                        sprintf("%s://%s:%d/api2/thumbnail_hash/%s/%s", $uri->getScheme(), $uri->getHost(), $uri->getPort(), 'small', $url),
                        $hashes
                    );
                }
                $payload = array(
                    'coverURLs' => $urls
                );
                $response->getBody()->write(json_encode($payload));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->get('/thumbnail_hash/{size}/{hash}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
                if (!in_array($args['size'], ['small', 'normal'])) {
                    throw new \Spieldose\Exception\InvalidParamsException('size');
                }
                $logger = $this->get(\Spieldose\Logger\HTTPRequestLogger::class);
                $settings = $this->get('settings')['thumbnails'];
                $thumbnail = new \aportela\RemoteThumbnailCacheWrapper\Thumbnail($logger, $settings['path'], $args['hash']);
                $thumbnailPath = $thumbnail->getThumbnailLocalPath($settings['sizes'][$args['size']]['width'], $settings['sizes'][$args['size']]['height']);
                if (!empty($thumbnailPath) && file_exists(($thumbnailPath))) {
                    $filesize = filesize($thumbnailPath);
                    $f = fopen($thumbnailPath, 'r');
                    fseek($f, 0);
                    $data = fread($f, $filesize);
                    fclose($f);
                    $response->getBody()->write($data);
                    return $response
                        ->withHeader('Content-Type', 'image/jpeg')
                        ->withHeader('Content-Length', $filesize)
                        ->withHeader('ETag', sha1($args['hash'] . $thumbnailPath . $filesize))
                        ->withHeader('Cache-Control', 'max-age=86400')
                        ->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\NotFoundException('Invalid / empty path for hash: ' . $args['hash']);
                }
            });
        }
    )->add(\Spieldose\Middleware\JWT::class)
        ->add(\Spieldose\Middleware\APIExceptionCatcher::class);
};

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
                    'lastFMAPIKey' => $settings['lastFM']['apiKey'],
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

            $group->get('/user/profile', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $payload = json_encode([
                    'user' => [
                        'id' => \Spieldose\User::getUserId(),
                        'email' => \Spieldose\User::getUserEmail()
                    ]
                ]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });

            $group->post('/user/profile', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $u = new \Spieldose\User(\Spieldose\User::getUserId(), $params['email'] ?? '', $params['password'] ?? '');
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                $u->update($db);
                \Spieldose\User::setSessionVars($u->id, $u->email);
                $payload = json_encode([
                    'user' => [
                        'id' => \Spieldose\User::getUserId(),
                        'email' => \Spieldose\User::getUserEmail()
                    ]
                ]);
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

            $group->post('/artists/', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                if (!\Spieldose\User::isLogged()) {
                    throw new \Spieldose\Exception\AuthenticationMissingException();
                }
                $logger = $this->get(\Spieldose\Logger\HTTPRequestLogger::class);
                $logger->info($request->getMethod() . ' ' . $request->getUri()->getPath());
                $db = $this->get(\aportela\DatabaseWrapper\DB::class);
                $filter = array(
                    "q" => $params['q'] ?? null
                );
                $pager = new \Spieldose\Helper\Pager(
                    $params['pager']['currentPage'] ?? 1,
                    $params['pager']['resultsPage'] ?? 32
                );
                $sort = new \Spieldose\Helper\Sort(
                    "name",
                    $params['sort']['order'] ?? \Spieldose\Helper\Sort::ASCENDING_ORDER
                );
                $data = \Spieldose\Artist::search($db, $pager, $sort, $filter);
                $payload = array("results" => $data);
                $response->getBody()->write(json_encode($payload));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            });
            $group->get('/artist/{name}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
                $logger = $this->get(\Spieldose\Logger\HTTPRequestLogger::class);
                $logger->info($request->getMethod() . ' ' . $request->getUri()->getPath());
                if (!empty($args['name'])) {
                    $artist = new \aportela\MusicBrainzWrapper\Artist($logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
                    $results = $artist->search($args['name'], 1);
                    $payload = array();
                    if (count($results) == 1 && !empty($results[0]->mbId)) {
                        $artist->get($results[0]->mbId);
                        $wiki = new \aportela\HTTPRequestWrapper\HTTPRequest($logger);
                        $wikiResponse = $wiki->GET('https://en.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exintro&explaintext&redirects=1&titles=' . urlencode($artist->name ?? $args['name']));
                        //$wikiResponse = $wiki->GET('https://en.wikipedia.org/w/api.php?action=parse&page=' . urlencode($artist->name) . '&format=json');
                        $payload = array(
                            'MusicBrainz' => json_decode($artist->raw),
                            'LastFM' => null,
                            'Wikipedia' => $wikiResponse->code == 200 ? json_decode($wikiResponse->body) : null
                        );
                    }
                    $response->getBody()->write(json_encode($payload));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            });

            /* metrics */

            $group->post('/metrics/top_played_tracks', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $metrics = \Spieldose\Metrics::GetTopPlayedTracks(
                    $this->get(\aportela\DatabaseWrapper\DB::class),
                    array(
                        "fromDate" => $params["fromDate"] ?? "",
                        "toDate" => $params["toDate"] ?? "",
                        "artist" => $params["artist"] ?? "",
                    ),
                    $params["count"] ?? 5
                );
                $payload = json_encode(['metrics' => $metrics]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            });

            $group->post('/metrics/top_artists', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $metrics = \Spieldose\Metrics::GetTopArtists(
                    $this->get(\aportela\DatabaseWrapper\DB::class),
                    array(
                        "fromDate" => $params["fromDate"] ?? "",
                        "toDate" => $params["toDate"] ?? "",
                    ),
                    $params["count"] ?? 5
                );
                $payload = json_encode(['metrics' => $metrics]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            });

            $group->post('/metrics/top_albums', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $metrics = \Spieldose\Metrics::GetTopAlbums(
                    $this->get(\aportela\DatabaseWrapper\DB::class),
                    array(
                        "fromDate" => $params["fromDate"] ?? "",
                        "toDate" => $params["toDate"] ?? "",
                    ),
                    $params["count"] ?? 5
                );
                $payload = json_encode(['metrics' => $metrics]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            });

            $group->post('/metrics/top_genres', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $metrics = \Spieldose\Metrics::GetTopGenres(
                    $this->get(\aportela\DatabaseWrapper\DB::class),
                    array(
                        "fromDate" => $params["fromDate"] ?? "",
                        "toDate" => $params["toDate"] ?? "",
                    ),
                    $params["count"] ?? 5
                );
                $payload = json_encode(['metrics' => $metrics]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            });

            $group->post('/metrics/recently_added', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $entity = $params["entity"] ?? "";
                if (!empty($entity)) {
                    switch ($entity) {
                        case "tracks":
                            $metrics = \Spieldose\Metrics::GetRecentlyAddedTracks(
                                $this->get(\aportela\DatabaseWrapper\DB::class),
                                array(),
                                $params["count"] ?? 5
                            );
                            break;
                        case "artists":
                            $metrics = \Spieldose\Metrics::GetRecentlyAddedArtists(
                                $this->get(\aportela\DatabaseWrapper\DB::class),
                                array(),
                                $params["count"] ?? 5
                            );
                            break;
                        case "albums":
                            $metrics = \Spieldose\Metrics::GetRecentlyAddedAlbums(
                                $this->get(\aportela\DatabaseWrapper\DB::class),
                                array(),
                                $params["count"] ?? 5
                            );

                            break;
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("entity");
                }
                $payload = json_encode(['metrics' => $metrics]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            });

            $group->post('/metrics/recently_played', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $entity = $params["entity"] ?? "";
                if (!empty($entity)) {
                    switch ($entity) {
                        case "tracks":
                            $metrics = \Spieldose\Metrics::GetRecentlyPlayedTracks(
                                $this->get(\aportela\DatabaseWrapper\DB::class),
                                array(),
                                $params["count"] ?? 5
                            );
                            break;
                        case "artists":
                            $metrics = \Spieldose\Metrics::GetRecentlyPlayedArtists(
                                $this->get(\aportela\DatabaseWrapper\DB::class),
                                array(),
                                $params["count"] ?? 5
                            );
                            break;
                        case "albums":
                            $metrics = \Spieldose\Metrics::GetRecentlyPlayedAlbums(
                                $this->get(\aportela\DatabaseWrapper\DB::class),
                                array(),
                                $params["count"] ?? 5
                            );
                            break;
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("entity");
                }
                $payload = json_encode(['metrics' => $metrics]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            });

            $group->post('/metrics/play_stats_by_hour', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $metrics = \Spieldose\Metrics::GetPlayStatsByHour(
                    $this->get(\aportela\DatabaseWrapper\DB::class),
                    array()
                );
                $payload = json_encode(['metrics' => $metrics]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            });

            $group->post('/metrics/play_stats_by_weekday', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $metrics = \Spieldose\Metrics::GetPlayStatsByWeekDay(
                    $this->get(\aportela\DatabaseWrapper\DB::class),
                    array()
                );
                $payload = json_encode(['metrics' => $metrics]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            });

            $group->post('/metrics/play_stats_by_month', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $metrics = \Spieldose\Metrics::GetPlayStatsByMonth(
                    $this->get(\aportela\DatabaseWrapper\DB::class),
                    array()
                );
                $payload = json_encode(['metrics' => $metrics]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            });

            $group->post('/metrics/play_stats_by_year', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $metrics = \Spieldose\Metrics::GetPlayStatsByYear(
                    $this->get(\aportela\DatabaseWrapper\DB::class),
                    array()
                );
                $payload = json_encode(['metrics' => $metrics]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            });

            /* metrics */
        }
    )->add(\Spieldose\Middleware\JWT::class)
        ->add(\Spieldose\Middleware\APIExceptionCatcher::class);
};

<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response, array $args) {
        $dbh = $this->get(\aportela\DatabaseWrapper\DB::class);
        if (!$dbh->isSchemaInstalled()) {
            // TODO: check upgrades
            $settings = $this->get('settings');
            $queryParams = $request->getQueryParams();
            $launched = isset($queryParams["launch"]);
            $installerException = null;
            $installOK = false;
            if ($launched) {
                $logger = $this->get(\Spieldose\Logger\InstallerLogger::class);
                $missingExtensions = array_diff($settings["phpRequiredExtensions"], get_loaded_extensions());
                if (count($missingExtensions) < 1) {
                    $pathErrors = [];
                    if (!file_exists($settings['thumbnails']['artists']['basePath'])) {
                        if (!@mkdir($settings['thumbnails']['artists']['basePath'], 0750, true)) {
                            $pathErrors[] = $settings['thumbnails']['artists']['basePath'];
                            $logger->critical("Error creating artist thumbnail basePath: " . $settings['thumbnails']['artists']['basePath']);
                        }
                    }
                    if (!file_exists($settings['thumbnails']['albums']['basePath'])) {
                        if (!@mkdir($settings['thumbnails']['albums']['basePath'], 0750, true)) {
                            $pathErrors[] = $settings['thumbnails']['albums']['basePath'];
                            $logger->critical("Error creating album thumbnail basePath: " . $settings['thumbnails']['albums']['basePath']);
                        }
                    }
                    if (!file_exists($settings['thumbnails']['radioStations']['basePath'])) {
                        if (!@mkdir($settings['thumbnails']['radioStations']['basePath'], 0750, true)) {
                            $pathErrors[] = $settings['thumbnails']['radioStations']['basePath'];
                            $logger->critical("Error creating radio station thumbnail basePath: " . $settings['thumbnails']['radioStations']['basePath']);
                        }
                    }
                    if (count($pathErrors) == 0) {
                        $dbh = $this->get(\aportela\DatabaseWrapper\DB::class);
                        try {
                            if ($dbh->installSchema()) {
                                $currentVersion = $dbh->upgradeSchema();
                                if ($currentVersion !== -1) {
                                    $installOK = true;
                                } else {
                                    unlink($settings['paths']['database']);
                                }
                            }
                        } catch (\Throwable $e) {
                            $installerException = [
                                'type' => get_class($e),
                                'message' => $e->getMessage(),
                                'file' => $e->getLine(),
                                'line' => $e->getFile()
                            ];
                            $parent = $e->getPrevious();
                            if ($parent) {
                                $installerException['parent'] = ['type' => get_class($parent), 'message' => $parent->getMessage(), 'file' => $parent->getFile(), 'line' => $parent->getLine()];
                            }
                        } finally {
                        }
                    }
                } else {
                    $logger->critical("Error: missing php extension/s: ", implode(", ", $missingExtensions));
                }
            }
            $dbh->close();
            if (!$installOK && file_exists($settings['paths']['database'])) {
                unlink($settings['paths']['database']);
            }
            return $this->get('Twig')->render($response, 'index-install.html.twig', ["launched" => $launched, "missingExtensions" => $missingExtensions ?? [], "installOK" => $installOK ?? false, "installerException" => $installerException, "pathErrors" => $pathErrors ?? []]);
        } else if ($dbh->getCurrentSchemaVersion() < $dbh->getUpgradeSchemaVersion()) {
            return $this->get('Twig')->render($response, 'index-upgrade.html.twig', ["launched" => false, "missingExtensions" => $missingExtensions ?? [], "installOK" => $installOK ?? false, "installerException" => null, "pathErrors" => $pathErrors ?? []]);
        } else {
            $dbh->close();
            return $this->get('Twig')->render($response, 'index-quasar.html.twig', []);
        }
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
                    $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                    if (\Spieldose\User::isEmailUsed($dbh, $params["email"] ?? "")) {
                        throw new \Spieldose\Exception\AlreadyExistsException("email");
                    } else if (\Spieldose\User::isNameUsed($dbh, $params["name"] ?? "")) {
                        throw new \Spieldose\Exception\AlreadyExistsException("name");
                    } else {
                        $user = new \Spieldose\User(
                            $params["id"] ?? "",
                            $params["email"] ?? "",
                            $params["password"] ?? "",
                            $params["name"] ?? ""
                        );
                        $user->add($dbh);
                        $payload = json_encode([]);
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                    }
                } else {
                    throw new \Spieldose\Exception\AccessDeniedException("");
                }
            });

            $group->post('/user/sign-in', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $user = new \Spieldose\User(
                    "",
                    $params["email"] ?? "",
                    $params["password"] ?? "",
                    $params["name"] ?? ""
                );
                $user->signIn($dbh);
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

            $group->post('/global_search', function (Request $request, Response $response, array $args) {
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $params = $request->getParsedBody();
                $sortItems = [];
                $sortItems[] = new \aportela\DatabaseBrowserWrapper\SortItem(
                    (isset($params["sort"]) && isset($params["sort"]["field"]) && !empty($params["sort"]["field"])) ? $params["sort"]["field"] : "title",
                    (isset($params["sort"]) && isset($params["sort"]["order"]) && $params["sort"]["order"] == "DESC") ? \aportela\DatabaseBrowserWrapper\Order::DESC : \aportela\DatabaseBrowserWrapper\Order::ASC,
                    true
                );
                $sort = new \aportela\DatabaseBrowserWrapper\Sort($sortItems);
                $pager = new \aportela\DatabaseBrowserWrapper\Pager(true, $params["pager"]["currentPageIndex"] ?? 1, $params["pager"]["resultsPage"] ?? 3);
                $data = array();
                $filter = array(
                    "title" => $params["filter"]["text"] ?? "",
                );
                $result = \Spieldose\Entities\Track::search($dbh, $filter, $sort, $pager);
                $data["tracks"] = $result->items;
                $sortItems = [];
                $sortItems[] = new \aportela\DatabaseBrowserWrapper\SortItem(
                    (isset($params["sort"]) && isset($params["sort"]["field"]) && !empty($params["sort"]["field"])) ? $params["sort"]["field"] : "name",
                    (isset($params["sort"]) && isset($params["sort"]["order"]) && $params["sort"]["order"] == "DESC") ? \aportela\DatabaseBrowserWrapper\Order::DESC : \aportela\DatabaseBrowserWrapper\Order::ASC,
                    true
                );
                $sort = new \aportela\DatabaseBrowserWrapper\Sort($sortItems);
                $filter = array(
                    "name" => $params["filter"]["text"] ?? "",
                );
                $result = \Spieldose\Entities\Artist::search($dbh, $filter, $sort, $pager);
                $data["artists"] = $result->items;
                $sortItems = [];
                $sortItems[] = new \aportela\DatabaseBrowserWrapper\SortItem(
                    (isset($params["sort"]) && isset($params["sort"]["field"]) && !empty($params["sort"]["field"])) ? $params["sort"]["field"] : "title",
                    (isset($params["sort"]) && isset($params["sort"]["order"]) && $params["sort"]["order"] == "DESC") ? \aportela\DatabaseBrowserWrapper\Order::DESC : \aportela\DatabaseBrowserWrapper\Order::ASC,
                    true
                );
                $sort = new \aportela\DatabaseBrowserWrapper\Sort($sortItems);
                $filter = array(
                    "title" => $params["filter"]["text"] ?? "",
                );
                $result = \Spieldose\Entities\Album::search($dbh, $filter, $sort, $pager, true);
                $data["albums"] = $result->items;
                $payload = json_encode(["data" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/track/{id}', function (Request $request, Response $response, array $args) {
                if (!empty($args['id'])) {
                    $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                    $track = new \Spieldose\Entities\Track($args['id']);
                    $track->get($dbh);
                    $payload = json_encode(["track" => $track]);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/track/search', function (Request $request, Response $response, array $args) {
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $params = $request->getParsedBody();
                $filter = array(
                    "text" => $params["filter"]["text"] ?? "",
                    "title" => $params["filter"]["title"] ?? "",
                    "path" => $params["filter"]["path"] ?? "",
                    "playlistId" => $params["filter"]["playlistId"] ?? "",
                    "albumMbId" => $params["filter"]["albumMbId"] ?? "",
                    "albumTitle" => $params["filter"]["albumTitle"] ?? "",
                    "artistName" => $params["filter"]["artistName"] ?? "",
                    "year" => $params["filter"]["year"] ?? null,
                );
                $sortItems = [];
                if ($params["sort"]["random"]) {
                    $sortItems[] = new \aportela\DatabaseBrowserWrapper\SortItemRandom();
                } elseif (isset($params["filter"]["albumMbId"]) && !empty($params["filter"]["albumMbId"])) {
                    $sortItems[] = new \aportela\DatabaseBrowserWrapper\SortItem("trackNumber", \aportela\DatabaseBrowserWrapper\Order::ASC, true);
                } else {
                    $sortItems[] = new \aportela\DatabaseBrowserWrapper\SortItem(
                        (isset($params["sort"]) && isset($params["sort"]["field"]) && !empty($params["sort"]["field"])) ? $params["sort"]["field"] : "name",
                        (isset($params["sort"]) && isset($params["sort"]["order"]) && $params["sort"]["order"] == "DESC") ? \aportela\DatabaseBrowserWrapper\Order::DESC : \aportela\DatabaseBrowserWrapper\Order::ASC,
                        true
                    );
                }
                $sort = new \aportela\DatabaseBrowserWrapper\Sort($sortItems);
                $pager = new \aportela\DatabaseBrowserWrapper\Pager($params["pager"]["resultsPage"] != 0, $params["pager"]["currentPageIndex"] ?? 1, $params["pager"]["resultsPage"]);
                $data = \Spieldose\Entities\Track::search($dbh, $filter, $sort, $pager);
                $payload = json_encode(["data" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/track/increase_play_count/{id}', function (Request $request, Response $response, array $args) {
                $track = new \Spieldose\Entities\Track($args["id"]);
                $track->increasePlayCount($this->get(\aportela\DatabaseWrapper\DB::class));
                $payload = json_encode([]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/track/set_favorite/{id}', function (Request $request, Response $response, array $args) {
                $track = new \Spieldose\Entities\Track($args["id"]);
                $track->toggleFavorite($this->get(\aportela\DatabaseWrapper\DB::class), true);
                $payload = json_encode(["favorited" => $track->favorited]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/track/unset_favorite/{id}', function (Request $request, Response $response, array $args) {
                $track = new \Spieldose\Entities\Track($args["id"]);
                $track->toggleFavorite($this->get(\aportela\DatabaseWrapper\DB::class), false);
                $payload = json_encode(["favorited" => null]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/thumbnail/{size}/remote/{entity}/', function (Request $request, Response $response, array $args) {
                $queryParams = $request->getQueryParams();
                if (isset($queryParams["url"]) && !empty($queryParams["url"]) && filter_var($queryParams["url"], FILTER_VALIDATE_URL)) {
                    if (!in_array($args['size'], ['small', 'normal'])) {
                        throw new \Spieldose\Exception\InvalidParamsException('size');
                    }
                    if (!in_array($args['entity'], ['artist', 'album', 'radiostation'])) {
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
                        case 'radiostation':
                            $settings = $this->get('settings')['thumbnails']['radioStations'];
                            break;
                    }
                    //$cachedETAG = $request->getHeaderLine('HTTP_IF_NONE_MATCH');
                    $logger = $this->get(\Spieldose\Logger\ThumbnailLogger::class);
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
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/thumbnail/{size}/local/{entity}/', function (Request $request, Response $response, array $args) {
                $queryParams = $request->getQueryParams();
                if (isset($queryParams["path"]) && !empty($queryParams["path"])) {
                    if (!in_array($args['size'], ['small', 'normal'])) {
                        throw new \Spieldose\Exception\InvalidParamsException('size');
                    }
                    if (!in_array($args['entity'], ['album'])) {
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
                    $thumbnail = new \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail($logger, $settings['basePath']);
                    $thumbnail->setDimensions($settings['sizes'][$args['size']]['width'], $settings['sizes'][$args['size']]['height']);
                    $thumbnail->setQuality($settings['sizes'][$args['size']]['quality']);
                    $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                    $local = \Spieldose\Entities\Album::getAlbumLocalPathCoverFromPathId($dbh, $queryParams["path"]);
                    if ($thumbnail->getFromLocalFilesystem($local) && !empty($thumbnail->path) && file_exists(($thumbnail->path))) {
                        $filesize = filesize($thumbnail->path);
                        $f = fopen($thumbnail->path, 'r');
                        fseek($f, 0);
                        $data = fread($f, $filesize);
                        fclose($f);
                        $response->getBody()->write($data);
                        return $response
                            ->withHeader('Content-Type', 'image/jpeg')
                            ->withHeader('Content-Length', $filesize)
                            ->withHeader('ETag', sha1($queryParams["path"] . $thumbnail->path . $filesize))
                            ->withHeader('Cache-Control', 'max-age=86400')
                            ->withStatus(200);
                    } else {
                        throw new \Spieldose\Exception\NotFoundException('Invalid / empty path for path: ' . $queryParams["path"]);
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('Invalid / empty path param');
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/track/thumbnail/{size}/{id}', function (Request $request, Response $response, array $args) {
                if (!in_array($args['size'], ['small', 'normal'])) {
                    throw new \Spieldose\Exception\InvalidParamsException('size');
                }
                $settings = $this->get('settings')['thumbnails'];
                //$cachedETAG = $request->getHeaderLine('HTTP_IF_NONE_MATCH');
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $logger = $this->get(\Spieldose\Logger\ThumbnailLogger::class);
                $localPathNormalSize = null;
                try {
                    $localPathNormalSize = \Spieldose\Track::getLocalThumbnail($dbh, $logger, $args['id'], $settings['sizes']['normal']['quality'], $settings['sizes']['normal']['width'], $settings['sizes']['normal']['height']);
                } catch (\Spieldose\Exception\NotFoundException $e) {
                }
                if (empty($localPathNormalSize)) {
                    try {
                        $localPathNormalSize = \Spieldose\Track::getRemoteThumbnail($dbh, $logger, $args['id'], $settings['sizes']['normal']['quality'], $settings['sizes']['normal']['width'], $settings['sizes']['normal']['height']);
                    } catch (\Spieldose\Exception\NotFoundException $e) {
                    }
                }
                $localPathSmallSize = null;
                try {
                    $localPathSmallSize = \Spieldose\Track::getLocalThumbnail($dbh, $logger, $args['id'], $settings['sizes']['small']['quality'], $settings['sizes']['small']['width'], $settings['sizes']['small']['height']);
                } catch (\Spieldose\Exception\NotFoundException $e) {
                }
                if (empty($localPathSmallSize)) {
                    try {
                        $localPathSmallSize = \Spieldose\Track::getRemoteThumbnail($dbh, $logger, $args['id'], $settings['sizes']['small']['quality'], $settings['sizes']['small']['width'], $settings['sizes']['small']['height']);
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

            // TODO: add entity
            $group->get('/cache/thumbnail/{size}/{hash}', function (Request $request, Response $response, array $args) {
                if (!in_array($args['size'], ['small', 'normal'])) {
                    throw new \Spieldose\Exception\InvalidParamsException('size');
                }
                $logger = $this->get(\Spieldose\Logger\ThumbnailLogger::class);
                $settings = $this->get('settings')['thumbnails']['albums'];
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
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $params = $request->getParsedBody();
                $filter = new \aportela\DatabaseBrowserWrapper\Filter(
                    array(
                        "name" => $params["filter"]["name"] ?? null,
                        "genre" => $params["filter"]["genre"] ?? null
                    )
                );
                $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                    [
                        new \aportela\DatabaseBrowserWrapper\SortItem(
                            (isset($params["sort"]) && isset($params["sort"]["field"]) && !empty($params["sort"]["field"])) ? $params["sort"]["field"] : "name",
                            (isset($params["sort"]) && isset($params["sort"]["order"]) && $params["sort"]["order"] == "DESC") ? \aportela\DatabaseBrowserWrapper\Order::DESC : \aportela\DatabaseBrowserWrapper\Order::ASC,
                            true
                        )
                    ]
                );
                $pager = new \aportela\DatabaseBrowserWrapper\Pager(true, $params["pager"]["currentPageIndex"] ?? 1, $params["pager"]["resultsPage"]);
                $data = \Spieldose\Entities\Artist::search($dbh, $filter, $sort, $pager);
                $payload = json_encode(["data" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/artist', function (Request $request, Response $response, array $args) {
                $queryParams = $request->getQueryParams();
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $artist = new \Spieldose\Entities\Artist($dbh);
                $artist->mbId = $queryParams["mbId"] ?? null;
                $artist->name = $queryParams["name"] ?? null;
                if (!(empty($artist->mbId) && empty($artist->name))) {
                    $settings = $this->get('settings')['thumbnails']['albums'];
                    $artist->get($settings['useLocalCovers']);
                    $payload = json_encode(
                        [
                            'artist' => $artist
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("mbId,name");
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/artists_genres', function (Request $request, Response $response, array $args) {
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $filter = [];
                $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                    [
                        new \aportela\DatabaseBrowserWrapper\SortItem(
                            "name",
                            \aportela\DatabaseBrowserWrapper\Order::ASC,
                            true
                        )
                    ]
                );
                $pager = new \aportela\DatabaseBrowserWrapper\Pager(false, 1, 0);
                $data = \Spieldose\ArtistGenre::search($dbh, $filter, $sort, $pager);
                $payload = json_encode(
                    [
                        "genres" =>
                        array_map(
                            function ($result) {
                                return ($result->name);
                            },
                            $data->items
                        )
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/album/search', function (Request $request, Response $response, array $args) {
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $params = $request->getParsedBody();
                $filter = array(
                    "title" => $params["filter"]["title"] ?? null,
                    "albumArtistName" => $params["filter"]["albumArtistName"] ?? null,
                    "text" => $params["filter"]["text"] ?? null,
                    "year" => $params["filter"]["year"] ?? null

                );
                $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                    [
                        new \aportela\DatabaseBrowserWrapper\SortItem(
                            (isset($params["sort"]) && isset($params["sort"]["field"]) && !empty($params["sort"]["field"])) ? $params["sort"]["field"] : "title",
                            (isset($params["sort"]) && isset($params["sort"]["order"]) && $params["sort"]["order"] == "DESC") ? \aportela\DatabaseBrowserWrapper\Order::DESC : \aportela\DatabaseBrowserWrapper\Order::ASC,
                            true
                        )
                    ]
                );
                $settings = $this->get('settings')['thumbnails']['albums'];
                $pager = new \aportela\DatabaseBrowserWrapper\Pager(true, $params["pager"]["currentPageIndex"] ?? 1, $params["pager"]["resultsPage"]);
                $data = \Spieldose\Entities\Album::search($dbh, $filter, $sort, $pager, $settings['useLocalCovers']);
                $payload = json_encode(["data" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/album', function (Request $request, Response $response, array $args) {
                $queryParams = $request->getQueryParams();
                $dbh = $this->get(\aportela\DatabaseWrapper\DB::class);
                $album = new \Spieldose\Entities\Album(
                    $queryParams["mbid"] ?? null,
                    $queryParams["title"] ?? null,
                    $queryParams["year"] ?? null,
                    (object) ["mbId" => $queryParams["artistMBId"] ?? null, "name" => $queryParams["artistName"] ?? null]
                );
                $settings = $this->get('settings')['thumbnails']['albums'];
                $album->get($dbh, $settings['useLocalCovers']);
                $payload = json_encode(
                    [
                        'album' => $album
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/album/small_random_covers/{count:[0-9]+}', function (Request $request, Response $response, array $args) {
                $settings = $this->get('settings')['thumbnails']['albums'];
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
                        fn ($hash) =>
                        sprintf(\Spieldose\API::CACHED_HASH_SMALL_THUMBNAIL, $hash),
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
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/path/tree', function (Request $request, Response $response, array $args) {
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $data = \Spieldose\Path::getTree($dbh);
                $payload = json_encode(["items" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/metrics/tracks', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $filter = $params["filter"] ?? [];
                $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                    [
                        new \aportela\DatabaseBrowserWrapper\SortItem($params["sortField"], \aportela\DatabaseBrowserWrapper\Order::DESC, false)
                    ]
                );
                $pager = new \aportela\DatabaseBrowserWrapper\Pager();
                $pager->enabled = false;
                $pager->resultsPage = $params["count"] ?? 5;
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $data = \Spieldose\Metrics::searchTracks($dbh, $filter, $sort, $pager);
                $payload = json_encode(["data" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/metrics/artists', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $filter = $params["filter"] ?? [];
                $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                    [
                        new \aportela\DatabaseBrowserWrapper\SortItem($params["sortField"], \aportela\DatabaseBrowserWrapper\Order::DESC, false)
                    ]
                );
                $pager = new \aportela\DatabaseBrowserWrapper\Pager();
                $pager->enabled = false;
                $pager->resultsPage = $params["count"] ?? 5;
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $data = \Spieldose\Metrics::searchArtists($dbh, $filter, $sort, $pager);
                $payload = json_encode(["data" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/metrics/albums', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $filter = $params["filter"] ?? [];
                $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                    [
                        new \aportela\DatabaseBrowserWrapper\SortItem($params["sortField"], \aportela\DatabaseBrowserWrapper\Order::DESC, false)
                    ]
                );
                $pager = new \aportela\DatabaseBrowserWrapper\Pager();
                $pager->enabled = false;
                $pager->resultsPage = $params["count"] ?? 5;
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $data = \Spieldose\Metrics::searchAlbums($dbh, $filter, $sort, $pager);
                $payload = json_encode(["data" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/metrics/genres', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $filter = $params["filter"] ?? [];
                $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                    [
                        new \aportela\DatabaseBrowserWrapper\SortItem($params["sortField"], \aportela\DatabaseBrowserWrapper\Order::DESC, false)
                    ]
                );
                $pager = new \aportela\DatabaseBrowserWrapper\Pager();
                $pager->enabled = false;
                $pager->resultsPage = $params["count"] ?? 5;
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $data = \Spieldose\Metrics::searchGenres($dbh, $filter, $sort, $pager);
                $payload = json_encode(["data" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/metrics/date_range', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $filter = $params["filter"] ?? [];
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $data = \Spieldose\Metrics::searchPlaysByDateRange($dbh, $filter);
                $payload = json_encode(["data" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/metrics/by_user', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $filter = $params["filter"] ?? [];
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $data = \Spieldose\Metrics::searchPlaysByUser($dbh, $filter);
                $payload = json_encode(["data" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/playlist/search', function (Request $request, Response $response, array $args) {
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $params = $request->getParsedBody();
                // TODO: include this check on all search api methods
                if (!empty($params["filter"])) {
                    $filter = new \aportela\DatabaseBrowserWrapper\Filter(
                        array(
                            "name" => $params["filter"]["name"] ?? "",
                            "userId" => $params["filter"]["userId"] ?? "",
                            "type" => $params["filter"]["type"] ?? ""
                        )
                    );
                    $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                        [
                            new \aportela\DatabaseBrowserWrapper\SortItem(
                                (isset($params["sort"]) && isset($params["sort"]["field"]) && !empty($params["sort"]["field"])) ? $params["sort"]["field"] : "name",
                                (isset($params["sort"]) && isset($params["sort"]["order"]) && $params["sort"]["order"] == "DESC") ? \aportela\DatabaseBrowserWrapper\Order::DESC : \aportela\DatabaseBrowserWrapper\Order::ASC,
                                true
                            )
                        ]
                    );
                    $pager = new \aportela\DatabaseBrowserWrapper\Pager(true, $params["pager"]["currentPageIndex"] ?? 1, $params["pager"]["resultsPage"]);
                    $data = \Spieldose\Playlist::search($dbh, $filter, $sort, $pager);
                    $payload = json_encode(["data" => $data]);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("filter");
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/playlist/add', function (Request $request, Response $response, array $args) {
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $params = $request->getParsedBody();
                $playlist = new \Spieldose\Playlist(
                    $params["playlist"]["id"] ?? "",
                    $params["playlist"]["name"] ?? "",
                    $params["playlist"]["tracks"] ?? [],
                    $params["playlist"]["public"] ?? false
                );
                $playlist->add($dbh);
                $payload = json_encode(["playlist" => $params["playlist"]]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/playlist/update', function (Request $request, Response $response, array $args) {
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $params = $request->getParsedBody();
                $playlist = new \Spieldose\Playlist(
                    $params["playlist"]["id"] ?? "",
                    $params["playlist"]["name"] ?? "",
                    $params["playlist"]["tracks"] ?? [],
                    $params["playlist"]["public"] ?? false
                );
                $playlist->update($dbh);
                $payload = json_encode(["playlist" => $params["playlist"]]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->delete('/playlist/{id}', function (Request $request, Response $response, array $args) {
                if (!empty($args['id'])) {
                    $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                    $playlist = new \Spieldose\Playlist(
                        $args['id'] ?? "",
                        "",
                        [],
                        false
                    );
                    $playlist->remove($dbh);
                    $payload = json_encode([]);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/playlist/{id}', function (Request $request, Response $response, array $args) {
                if (!empty($args['id'])) {
                    $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                    $playlist = new \Spieldose\Playlist(
                        $args['id'] ?? "",
                        "",
                        [],
                        false
                    );
                    $playlist->get($dbh);
                    $payload = json_encode(["playlist" => $playlist]);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/current_playlist', function (Request $request, Response $response, array $args) {
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $currentPlaylist = new \Spieldose\CurrentPlaylist();
                $currentPlaylist->get($dbh);
                $payload = json_encode($currentPlaylist);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/current_playlist/sort/random', function (Request $request, Response $response, array $args) {
                $queryParams = $request->getQueryParams();
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $currentPlaylist = new \Spieldose\CurrentPlaylist();
                $payload = json_encode($currentPlaylist->randomSort($dbh, isset($queryParams["shuffle"]) && $queryParams["shuffle"] == "true"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/current_playlist/sort/indexes', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $currentPlaylist = new \Spieldose\CurrentPlaylist();
                $indexes = [];
                if (isset($params["indexes"]) && is_array($params["indexes"])) {
                    $indexes = $params["indexes"];
                }
                $payload = json_encode($currentPlaylist->sortByIndexes($dbh, $indexes, isset($queryParams["shuffle"]) && $queryParams["shuffle"] == "true"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/current_playlist/current_element', function (Request $request, Response $response, array $args) {
                $queryParams = $request->getQueryParams();
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);;
                $currentPlaylist = new \Spieldose\CurrentPlaylist();
                $payload = json_encode($currentPlaylist->getCurrentElement($dbh, isset($queryParams["shuffle"]) && $queryParams["shuffle"] == "true"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/current_playlist/previous_element', function (Request $request, Response $response, array $args) {
                $queryParams = $request->getQueryParams();
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);;
                $currentPlaylist = new \Spieldose\CurrentPlaylist();
                $payload = json_encode($currentPlaylist->getPreviousElement($dbh, isset($queryParams["shuffle"]) && $queryParams["shuffle"] == "true"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/current_playlist/next_element', function (Request $request, Response $response, array $args) {
                $queryParams = $request->getQueryParams();
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $currentPlaylist = new \Spieldose\CurrentPlaylist();
                $payload = json_encode($currentPlaylist->getNextElement($dbh, isset($queryParams["shuffle"]) && $queryParams["shuffle"] == "true"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/current_playlist/element_at_index', function (Request $request, Response $response, array $args) {
                $queryParams = $request->getQueryParams();
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $currentPlaylist = new \Spieldose\CurrentPlaylist();
                $payload = json_encode($currentPlaylist->getElementAtIndex($dbh, $queryParams["index"] ?? -1));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/current_playlist/remove_element_at_index', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $currentPlaylist = new \Spieldose\CurrentPlaylist();
                $payload = json_encode($currentPlaylist->removeElementAtIndex($dbh, $params["index"] ?? -1, ((isset($params["shuffle"]) && $params["shuffle"] == "true") && $params["shuffle"])));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/current_playlist/discover_tracks', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $currentPlaylist = new \Spieldose\CurrentPlaylist();
                $payload = json_encode($currentPlaylist->discover($dbh, $params["count"] ?? 32, ((isset($params["shuffle"]) && $params["shuffle"] == "true") && $params["shuffle"])));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/current_playlist/set_tracks', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $dbh = $this->get(\aportela\DatabaseWrapper\DB::class);
                $currentPlaylist = new \Spieldose\CurrentPlaylist();
                $currentPlaylist->playlist->id = null;
                if (isset($params["trackIds"]) && is_array($params["trackIds"])) {
                    if (!$currentPlaylist->save($dbh, $params["trackIds"])) {
                        // TODO
                        throw new \Exception("save error");
                    }
                } else if (isset($params["album"]) && isset($params["album"]["mbId"]) && !empty($params["album"]["mbId"])) {
                    // TODO: search on another fields
                    $albumTrackIds = \Spieldose\Entities\Album::getTrackIds($dbh, ["mbId" => $params["album"]["mbId"]]);
                    if (!$currentPlaylist->save($dbh, $albumTrackIds)) {
                        // TODO
                        throw new \Exception("save error");
                    }
                } else if (isset($params["playlistId"]) && !empty($params["playlistId"])) {
                    $playlistTrackIds = \Spieldose\Playlist::getTrackIds($dbh, $params["playlistId"]);
                    $currentPlaylist->playlist->id = $params["playlistId"];
                    if (!$currentPlaylist->save($dbh, $playlistTrackIds)) {
                        // TODO
                        throw new \Exception("save error");
                    }
                } else if (isset($params["pathId"]) && !empty($params["pathId"])) {
                    $pathTracksIds = \Spieldose\Path::getTrackIds($dbh, $params["pathId"]);
                    if (!$currentPlaylist->save($dbh, $pathTracksIds)) {
                        // TODO
                        throw new \Exception("save error");
                    }
                }
                $payload = json_encode($currentPlaylist->getCurrentElement($dbh, (isset($params["shuffle"]) && $params["shuffle"] == "true")));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/current_playlist/append_tracks', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $currentPlaylist = new \Spieldose\CurrentPlaylist();
                if (isset($params["trackIds"]) && is_array($params["trackIds"])) {
                    if (!$currentPlaylist->append($dbh, $params["trackIds"])) {
                        // TODO
                        throw new \Exception("save error");
                    }
                } else if (isset($params["album"]) && isset($params["album"]["mbId"]) && !empty($params["album"]["mbId"])) {
                    // TODO: search on another fields
                    $albumTrackIds = \Spieldose\Entities\Album::getTrackIds($dbh, ["mbId" => $params["album"]["mbId"]]);
                    if (!$currentPlaylist->append($dbh, $albumTrackIds)) {
                        // TODO
                        throw new \Exception("save error");
                    }
                } else if (isset($params["playlistId"]) && !empty($params["playlistId"])) {
                    $playlistTrackIds = \Spieldose\Playlist::getTrackIds($dbh, $params["playlistId"]);
                    if (!$currentPlaylist->save($dbh, $playlistTrackIds)) {
                        // TODO
                        throw new \Exception("save error");
                    }
                }
                $payload = json_encode($currentPlaylist->getCurrentElement($dbh, (isset($params["shuffle"]) && $params["shuffle"] == "true")));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/current_playlist/set_radiostation', function (Request $request, Response $response, array $args) {
                $params = $request->getParsedBody();
                if (!empty($params["id"])) {
                    $dbh = $this->get(\aportela\DatabaseWrapper\DB::class);
                    $currentPlaylist = new \Spieldose\CurrentPlaylist();
                    $currentPlaylist->setRadiostation($dbh, $params["id"]);
                    $payload = json_encode($currentPlaylist->getCurrentElement($dbh, (isset($params["shuffle"]) && $params["shuffle"] == "true")));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("id");
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/radio_station/search', function (Request $request, Response $response, array $args) {
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $params = $request->getParsedBody();
                $filter = array(
                    "name" => $params["filter"]["name"] ?? ""
                );
                $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                    [
                        new \aportela\DatabaseBrowserWrapper\SortItem("name", \aportela\DatabaseBrowserWrapper\Order::ASC, true)
                    ]
                );
                $pager = new \aportela\DatabaseBrowserWrapper\Pager(true, $params["pager"]["currentPageIndex"] ?? 1, $params["pager"]["resultsPage"]);
                $data = [
                    "items" => include "../Spieldose/RadioStations.php"
                ];
                $payload = json_encode(["data" => $data]);
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/lyrics', function (Request $request, Response $response, array $args) {
                $queryParams = $request->getQueryParams();
                $dbh =  $this->get(\aportela\DatabaseWrapper\DB::class);
                $title = $queryParams["title"] ?? "";
                $artist = $queryParams["artist"] ?? "";
                $lyrics = new \Spieldose\Lyrics($this->get(\Spieldose\Logger\ScraperLogger::class));
                $payload = json_encode(
                    [
                        'lyrics' => $lyrics->get($dbh, $title, $artist) ? $lyrics->lyrics : null
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);
        }
    )->add(\Spieldose\Middleware\JWT::class)->add(\Spieldose\Middleware\APIExceptionCatcher::class);
};

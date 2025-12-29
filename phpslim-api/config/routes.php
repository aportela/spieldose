<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

return function (App $app): void {
    $app->get('/', function (Request $request, Response $response, array $args): \Psr\Http\Message\MessageInterface|\Psr\Http\Message\ResponseInterface {
        $filePath = dirname(__DIR__) . '/public/index.html';
        if (file_exists($filePath)) {
            $contents = file_get_contents($filePath);
            if (is_string($contents)) {
                $response->getBody()->write($contents);
                return $response->withHeader('Content-Type', 'text/html; charset=UTF-8');
            } else {
                $response->getBody()->write("Invalid html template");
                return $response->withStatus(500);
            }
        } else {
            return $response->withStatus(404);
        }
    });

    $app->group(
        '/api2',
        function (RouteCollectorProxy $group) use ($app): void {

            $container = $app->getContainer();
            if (!$container instanceof \Psr\Container\ContainerInterface) {
                throw new \RuntimeException("Error getting container");
            }

            $settings = new \Spieldose\Settings();
            $serverEnvironment = \Spieldose\Utils::GetServerEnvironment($settings);

            $group->get('/server_environment', function (Request $request, Response $response, array $args) use ($serverEnvironment): \Psr\Http\Message\MessageInterface {
                $payload = \Spieldose\Utils::getJSONPayload(
                    [
                        'serverEnvironment' => $serverEnvironment,
                    ]
                );
                $response->getBody()->write($payload);
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            });

            $group->group('/auth', function (RouteCollectorProxy $routeCollectorProxy) use ($container, $settings): void {
                $routeCollectorProxy->post('/register', function (Request $request, Response $response, array $args) use ($container, $settings): \Psr\Http\Message\MessageInterface {
                    if ($settings->allowSignUp()) {
                        $params = $request->getParsedBody();
                        if (! is_array($params)) {
                            throw new \Spieldose\Exception\InvalidParamsException();
                        }

                        $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                        if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                            throw new \RuntimeException("Failed to create database handler from container");
                        }

                        if (\Spieldose\User::isEmailUsed($dbh, array_key_exists("email", $params) && is_string($params["email"]) ? $params["email"] : "")) {
                            throw new \Spieldose\Exception\AlreadyExistsException("email");
                        } else {
                            $user = new \Spieldose\User(
                                array_key_exists("id", $params) && is_string($params["id"]) ? $params["id"] : "",
                                array_key_exists("email", $params) && is_string($params["email"]) ? $params["email"] : "",
                                array_key_exists("password", $params) && is_string($params["password"]) ? $params["password"] : ""
                            );
                            $user->add($dbh);
                            $payload = \Spieldose\Utils::getJSONPayload(
                                []
                            );
                            $response->getBody()->write($payload);
                            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                        }
                    } else {
                        throw new \Spieldose\Exception\AccessDeniedException("");
                    }
                });

                $routeCollectorProxy->post('/login', function (Request $request, Response $response, array $args) use ($container, $settings): \Psr\Http\Message\MessageInterface {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \Spieldose\Exception\InvalidParamsException();
                    }

                    $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                    if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                        throw new \RuntimeException("Failed to create database handler from container");
                    }

                    $logger = $container->get(\Spieldose\Logger\DefaultLogger::class);
                    if (! $logger instanceof \Spieldose\Logger\DefaultLogger) {
                        throw new \RuntimeException("Failed to create logger from container");
                    }

                    $user = new \Spieldose\User(
                        "",
                        array_key_exists("email", $params) && is_string($params["email"]) ? $params["email"] : "",
                        array_key_exists("password", $params) && is_string($params["password"]) ? $params["password"] : ""
                    );
                    $user->login($dbh);

                    $jwt = new \Spieldose\JWT($logger, $settings->getJWTPassphrase());

                    $currentTimestamp = time();
                    $accessToken = $jwt->encode(strval(\Spieldose\UserSession::getUserId()), $currentTimestamp + $settings->getAccessTokenExpirationTimeInSeconds());
                    $refreshToken = $jwt->encode(strval(\Spieldose\UserSession::getUserId()), $currentTimestamp + $settings->getRefreshTokenExpirationTimeInSeconds());
                    \Spieldose\UserSession::setAccessTokenData($accessToken, $currentTimestamp + $settings->getAccessTokenExpirationTimeInSeconds());
                    $payload = \Spieldose\Utils::getJSONPayload(
                        [
                            "accessToken" => [
                                "token" => $accessToken,
                                "expiresAtTimestamp" => $currentTimestamp + $settings->getAccessTokenExpirationTimeInSeconds(),
                            ],
                            "refreshToken" => [
                                "token" => $refreshToken,
                                "expiresAtTimestamp" => $currentTimestamp + $settings->getRefreshTokenExpirationTimeInSeconds(),
                            ],
                            "tokenType" => "Bearer",
                        ]
                    );
                    setcookie(
                        "refresh_token",
                        $refreshToken,
                        [
                            'expires' => $currentTimestamp + $settings->getRefreshTokenExpirationTimeInSeconds(),
                            'path' => '/api2/auth/renew_access_token',
                            'secure' => true,
                            'httponly' => true,
                            'samesite' => 'Strict',
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->post('/renew_access_token', function (Request $request, Response $response, array $args) use ($container, $settings): \Psr\Http\Message\MessageInterface {
                    $refreshToken = null;
                    if (isset($_COOKIE['refresh_token']) && ! empty($_COOKIE['refresh_token'])) {
                        $refreshToken = $_COOKIE['refresh_token'];
                    } else {
                        $params = $request->getParsedBody();
                        if (is_array($params) && array_key_exists("refreshToken", $params) && is_string($params["refreshToken"]) && ($params["refreshToken"] !== '' && $params["refreshToken"] !== '0')) {
                            $refreshToken = $params["refreshToken"];
                        }
                    }

                    if (! is_string($refreshToken) || ($refreshToken === '' || $refreshToken === '0')) {
                        throw new \Spieldose\Exception\UnauthorizedException("Missing refresh token (cookie/POST param)");
                    }

                    $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                    if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                        throw new \RuntimeException("Failed to create database handler from container");
                    }

                    $logger = $container->get(\Spieldose\Logger\DefaultLogger::class);
                    if (! $logger instanceof \Spieldose\Logger\DefaultLogger) {
                        throw new \RuntimeException("Failed to create logger from container");
                    }

                    \Spieldose\UserSession::clear();

                    $jwt = new \Spieldose\JWT($logger, $settings->getJWTPassphrase());
                    $decoded = null;
                    try {
                        $decoded = $jwt->decode($refreshToken);
                    } catch (\Firebase\JWT\ExpiredException $e) {
                        $logger->notice("JWT expired", [$e->getMessage()]);
                        throw new \Spieldose\Exception\UnauthorizedException("JWT expired");
                    } catch (\Throwable $e) {
                        $logger->notice("JWT decode error", [$e->getMessage()]);
                        throw new \Spieldose\Exception\UnauthorizedException("JWT decode error");
                    }

                    if (property_exists($decoded, "sub") && is_string($decoded->sub) && ($decoded->sub !== '' && $decoded->sub !== '0')) {
                        $user = new \Spieldose\User($decoded->sub);
                        $user->get($dbh);
                        $jwt = new \Spieldose\JWT($logger, $settings->getJWTPassphrase());
                        $currentTimestamp = time();
                        $accessToken = $jwt->encode(strval($user->id), $currentTimestamp + $settings->getAccessTokenExpirationTimeInSeconds());
                        \Spieldose\UserSession::setAccessTokenData($accessToken, $currentTimestamp + $settings->getAccessTokenExpirationTimeInSeconds());
                        $payload = \Spieldose\Utils::getJSONPayload(
                            [
                                "accessToken" => [
                                    "token" => $accessToken,
                                    "expiresAtTimestamp" => $currentTimestamp + $settings->getAccessTokenExpirationTimeInSeconds(),
                                ],
                                "tokenType" => "Bearer",
                            ]
                        );
                        $response->getBody()->write($payload);
                        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                    } else {
                        throw new \Spieldose\Exception\UnauthorizedException("Missing user id on JWT refresh token");
                    }
                });

                $routeCollectorProxy->post('/logout', function (Request $request, Response $response, array $args): \Psr\Http\Message\MessageInterface {
                    \Spieldose\User::logout();
                    $payload = \Spieldose\Utils::getJSONPayload(
                        []
                    );
                    setcookie(
                        "refresh_token",
                        "",
                        [
                            'expires' => time() - 3600,
                            'path' => '/api2/auth/renew_access_token',
                            'secure' => true,
                            'httponly' => true,
                            'samesite' => 'Strict',
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });
            });

            $group->group('/user', function (RouteCollectorProxy $routeCollectorProxy) use ($container): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $routeCollectorProxy->get('/profile', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $user = new \Spieldose\User(\Spieldose\UserSession::getUserId());
                    $user->get($dbh);
                    unset($user->password);
                    unset($user->passwordHash);
                    $payload = \Spieldose\Utils::getJSONPayload(
                        [
                            'user' => $user,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });

                $routeCollectorProxy->put('/profile', function (Request $request, Response $response, array $args) use ($dbh): \Psr\Http\Message\MessageInterface {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \Spieldose\Exception\InvalidParamsException();
                    }

                    if (! (array_key_exists("email", $params) && is_string($params["email"]))) {
                        throw new \Spieldose\Exception\InvalidParamsException("email");
                    }

                    $user = new \Spieldose\User(\Spieldose\UserSession::getUserId());
                    $user->get($dbh);
                    if ($params["email"] !== \Spieldose\UserSession::getEmail()) {
                        $tmpUser = new \Spieldose\User(
                            "",
                            $params["email"]
                        );
                        if ($tmpUser->exists($dbh)) {
                            throw new \Spieldose\Exception\AlreadyExistsException("email");
                        }
                    }

                    $user->email = $params["email"];
                    $user->password = array_key_exists("password", $params) && is_string($params["password"]) ? $params["password"] : "";
                    $user->update($dbh);
                    unset($user->password);
                    unset($user->passwordHash);
                    $payload = \Spieldose\Utils::getJSONPayload(
                        [
                            'user' => $user,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                });
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->group('/discover', function (RouteCollectorProxy $routeCollectorProxy) use ($container): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $routeCollectorProxy->post('/artists', function (Request $request, Response $response, array $args) use ($dbh) {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \Spieldose\Exception\InvalidParamsException();
                    }

                    $skipCount = true;
                    $browserResults = new \Spieldose\Browse\Artist($dbh)->browse(
                        \Spieldose\RouteHelper::getPagerFromParams($params),
                        new \aportela\DatabaseBrowserWrapper\Filter([]),
                        new \aportela\DatabaseBrowserWrapper\Sort([new \aportela\DatabaseBrowserWrapper\SortItemRandom()]),
                        $skipCount,
                    );
                    $payload = json_encode(
                        $skipCount ?
                            [
                                "artists" => $browserResults->items
                            ] :
                            [
                                "pager" => [
                                    "totalPages" => $browserResults->pager->getTotalPages(),
                                    "totalResults" => $browserResults->pager->getTotalResults()
                                ],
                                "artists" => $browserResults->items
                            ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->post('/albums', function (Request $request, Response $response, array $args) use ($dbh) {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \Spieldose\Exception\InvalidParamsException();
                    }
                    $skipCount = true;
                    $browserResults = new \Spieldose\Browse\Album($dbh)->browse(
                        \Spieldose\RouteHelper::getPagerFromParams($params),
                        new \aportela\DatabaseBrowserWrapper\Filter([]),
                        new \aportela\DatabaseBrowserWrapper\Sort([new \aportela\DatabaseBrowserWrapper\SortItemRandom()]),
                        $skipCount,
                    );
                    $payload = json_encode(
                        $skipCount ?
                            [
                                "albums" => $browserResults->items
                            ] :
                            [
                                "pager" => [
                                    "totalPages" => $browserResults->pager->getTotalPages(),
                                    "totalResults" => $browserResults->pager->getTotalResults()
                                ],
                                "albums" => $browserResults->items
                            ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->post('/path/{id}', function (Request $request, Response $response, array $args) use ($dbh) {
                    if (empty($args['id'])) {
                        throw new \Spieldose\Exception\InvalidParamsException("id");
                    }

                    $tree = new \Spieldose\Browse\Path($dbh)->getTree($args['id']);
                    $payload = json_encode(
                        [
                            "data" => [
                                "tree" => $tree
                            ]
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->get('/libraries', function (Request $request, Response $response, array $args) use ($dbh) {
                    $payload = json_encode(
                        [
                            "data" => [
                                "items" => new \Spieldose\Browse\Path($dbh)->getLibraries()
                            ]
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->group('/browse', function (RouteCollectorProxy $routeCollectorProxy) use ($container): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $routeCollectorProxy->post('/artists', function (Request $request, Response $response, array $args) use ($dbh) {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \Spieldose\Exception\InvalidParamsException();
                    }

                    $skipCount = \Spieldose\RouteHelper::skipCountTrueParamFound($params);
                    $browserResults = new \Spieldose\Browse\Artists($dbh)->browse(
                        \Spieldose\RouteHelper::getPagerFromParams($params),
                        \Spieldose\RouteHelper::getFilterFromParams($params),
                        \Spieldose\RouteHelper::getSortFromParams($params, "name", \aportela\DatabaseBrowserWrapper\Order::ASC, true),
                        $skipCount
                    );
                    $payload = json_encode(
                        $skipCount ?
                            [
                                "artists" => $browserResults->items
                            ] :
                            [
                                "pager" => [
                                    "totalPages" => $browserResults->pager->getTotalPages(),
                                    "totalResults" => $browserResults->pager->getTotalResults()
                                ],
                                "artists" => $browserResults->items
                            ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->post('/albums', function (Request $request, Response $response, array $args) use ($dbh) {
                    $params = $request->getParsedBody();
                    if (! is_array($params)) {
                        throw new \Spieldose\Exception\InvalidParamsException();
                    }
                    $skipCount = \Spieldose\RouteHelper::skipCountTrueParamFound($params);
                    $browserResults = new \Spieldose\Browse\Albums($dbh)->browse(
                        \Spieldose\RouteHelper::getPagerFromParams($params),
                        \Spieldose\RouteHelper::getFilterFromParams($params),
                        \Spieldose\RouteHelper::getSortFromParams($params, "title", \aportela\DatabaseBrowserWrapper\Order::ASC, true),
                        $skipCount
                    );
                    $payload = json_encode(
                        $skipCount ?
                            [
                                "albums" => $browserResults->items
                            ] :
                            [
                                "pager" => [
                                    "totalPages" => $browserResults->pager->getTotalPages(),
                                    "totalResults" => $browserResults->pager->getTotalResults()
                                ],
                                "albums" => $browserResults->items
                            ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->post('/path/{id}', function (Request $request, Response $response, array $args) use ($dbh) {
                    if (empty($args['id'])) {
                        throw new \Spieldose\Exception\InvalidParamsException("id");
                    }

                    $tree = new \Spieldose\Browse\Path($dbh)->getTree($args['id']);
                    $payload = json_encode(
                        [
                            "data" => [
                                "tree" => $tree
                            ]
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->get('/libraries', function (Request $request, Response $response, array $args) use ($dbh) {
                    $payload = json_encode(
                        [
                            "data" => [
                                "items" => new \Spieldose\Browse\Path($dbh)->getLibraries()
                            ]
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->group('/common', function (RouteCollectorProxy $routeCollectorProxy) use ($container): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $routeCollectorProxy->get('/musicbrainz_artist_genre_cloud', function (Request $request, Response $response, array $args) use ($dbh) {
                    $payload = json_encode(
                        [
                            "items" => \Spieldose\Entities\Artist::getMusicBrainzArtistGenreCloud($dbh)
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
                $routeCollectorProxy->get('/lastfm_artist_tag_cloud', function (Request $request, Response $response, array $args) use ($dbh) {
                    $payload = json_encode(
                        [
                            "items" => \Spieldose\Entities\Artist::getLastFMArtistTagCloud($dbh)
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/thumbnail/{action:local|remote}', function (Request $request, Response $response, array $args) use ($settings, $container) {
                $action = $args['action'];
                $queryParams = $request->getQueryParams();
                if (! is_array($queryParams)) {
                    throw new \Spieldose\Exception\InvalidParamsException();
                }
                if (! (array_key_exists("width", $queryParams) && is_numeric($queryParams["width"]) && $queryParams["width"] > 0)) {
                    throw new \Spieldose\Exception\InvalidParamsException("width");
                }

                if (! (array_key_exists("height", $queryParams) && is_numeric($queryParams["height"]) && $queryParams["height"] > 0)) {
                    throw new \Spieldose\Exception\InvalidParamsException("height");
                }

                if (! (array_key_exists("quality", $queryParams) && is_numeric($queryParams["quality"]) && $queryParams["quality"]) > 0  && $queryParams["quality"] <= 100) {
                    throw new \Spieldose\Exception\InvalidParamsException("quality");
                }
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }
                $logger = $container->get(\Spieldose\Logger\ThumbnailLogger::class);
                if (! $logger instanceof \Spieldose\Logger\ThumbnailLogger) {
                    throw new \RuntimeException("Failed to create logger from container");
                }
                $source = null;
                switch ($action) {
                    case "local":
                        if (! (array_key_exists("albumPathId", $queryParams) && is_string($queryParams["albumPathId"]))) {
                            throw new \Spieldose\Exception\InvalidParamsException("albumPathId");
                        }
                        $localCoverPath = \Spieldose\Entities\EntityImages::getAlbumLocalImagePath($dbh, $queryParams["albumPathId"]);
                        if (in_array($localCoverPath, [null, '', '0'], true)) {
                            throw new \Spieldose\Exception\NotFoundException("");
                        }
                        $source = new \aportela\RemoteThumbnailCacheWrapper\Source\LocalFilenameResource($localCoverPath);
                        break;
                    case "remote":
                        if (! (array_key_exists("url", $queryParams) && is_string($queryParams["url"]))) {
                            throw new \Spieldose\Exception\InvalidParamsException("url");
                        }
                        $source = new \aportela\RemoteThumbnailCacheWrapper\Source\URLSource($queryParams["url"]);
                        break;
                }
                $thumbnail = new \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail(
                    $logger,
                    $settings->getCachePath("Thumbnails"),
                    $source,
                    intval($queryParams["quality"]),
                    intval($queryParams["width"]),
                    intval($queryParams["height"])
                );
                $path = $thumbnail->get();
                if (is_string($path) && file_exists($path)) {
                    $filesize = filesize($path);
                    $etag = sha1($queryParams["url"] ?? $queryParams["albumPathId"] ?? "" . $path . $filesize);
                    $ifNoneMatchHeader = $request->getHeaderLine('If-None-Match');
                    if (! empty($ifNoneMatchHeader) && $ifNoneMatchHeader === $etag) {
                        return $response->withStatus(304);
                    } else {
                        $f = fopen($path, 'r');
                        fseek($f, 0);
                        $data = fread($f, $filesize);
                        fclose($f);
                        $response->getBody()->write($data);
                        return $response
                            ->withHeader('Content-Type', 'image/jpeg')
                            ->withHeader('Content-Length', (string) $filesize)
                            ->withHeader('Vary', 'If-None-Match')
                            ->withHeader('ETag', $etag)
                            ->withHeader('Last-Modified', gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT')
                            ->withHeader('Cache-Control', 'public, max-age=86400, must-revalidate')
                            ->withStatus(200);
                    }
                } else {
                    throw new \Spieldose\Exception\NotFoundException('Error getting local thumbnail path');
                }
            });

            $group->group('/file/{id}', function (RouteCollectorProxy $routeCollectorProxy) use ($container): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $routeCollectorProxy->get('/info', function (Request $request, Response $response, array $args) use ($dbh) {
                    if (empty($args['id'])) {
                        throw new \Spieldose\Exception\InvalidParamsException('id');
                    }
                    $fileId = $args['id'];
                    $file = new \Spieldose\Entities\File($fileId);
                    $file->get($dbh);
                    $payload = json_encode(
                        [
                            "file" => $file
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->post('/set_favorite', function (Request $request, Response $response, array $args) use ($dbh) {
                    if (empty($args['id'])) {
                        throw new \Spieldose\Exception\InvalidParamsException('id');
                    }
                    $fileId = $args['id'];
                    \Spieldose\Entities\PlayList\PlayList::toggleFavoriteFile($dbh, \Spieldose\UserSession::getUserId(), $fileId, true);
                    $payload = json_encode(
                        []
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->post('/unset_favorite', function (Request $request, Response $response, array $args) use ($dbh) {
                    if (empty($args['id'])) {
                        throw new \Spieldose\Exception\InvalidParamsException('id');
                    }
                    $fileId = $args['id'];
                    \Spieldose\Entities\PlayList\PlayList::toggleFavoriteFile($dbh, \Spieldose\UserSession::getUserId(), $fileId, false);
                    $payload = json_encode(
                        []
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->get('/{action:raw|download}', function (Request $request, Response $response, array $args): \Psr\Http\Message\MessageInterface {
                    if (empty($args['id'])) {
                        throw new \Spieldose\Exception\InvalidParamsException('id');
                    }
                    $fileId = $args['id'];
                    $action = $args['action'];
                    $file = new \Spieldose\File($this, $fileId);
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
                            preg_match('/bytes=(\d+)-(\d+)?/', (string) $_SERVER['HTTP_RANGE'], $matches);
                            $offset = intval($matches[1]);
                            $length = ((isset($matches[2])) ? intval($matches[2]) : $file->length) - $offset;
                        }

                        $response->getBody()->write($file->getData($offset, $length));
                        if ($partialContent) {
                            // output the right headers for partial content
                            return $response->withStatus(206)

                                ->withHeader('Content-Type', $file->mime ?: 'application/octet-stream')
                                ->withHeader('Content-Disposition', ($action === 'download' ? "attachment" : "inline") . '; filename="' . basename((string) $file->path) . '"')
                                ->withHeader('Content-Length', $file->length)
                                ->withHeader('Content-Range', 'bytes ' . $offset . '-' . ($offset + $length - 1) . '/' . $file->length)
                                ->withHeader('Accept-Ranges', 'bytes');
                        } else {
                            return $response->withStatus(200)
                                ->withHeader('Content-Type', $file->mime ?: "application/octet-stream")
                                ->withHeader('Content-Disposition', ($action === 'download' ? "attachment" : "inline") . '; filename="' . basename((string) $file->path) . '"')
                                ->withHeader('Content-Length', $file->length)
                                ->withHeader('Accept-Ranges', 'bytes');
                        }
                    } else {
                        throw new \Spieldose\Exception\NotFoundException('id');
                    }
                });
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->group('/track', function (RouteCollectorProxy $routeCollectorProxy) use ($container): void {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $routeCollectorProxy->get('/{id}/set_favorite', function (Request $request, Response $response, array $args) use ($dbh) {
                    $track = new \Spieldose\Entities\Track($args["id"]);
                    $track->toggleFavorite($dbh, true);

                    $payload = json_encode(
                        [
                            "favorited" => $track->favorited
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $routeCollectorProxy->get('/{id}/unset_favorite', function (Request $request, Response $response, array $args) use ($dbh) {
                    $track = new \Spieldose\Entities\Track($args["id"]);
                    $track->toggleFavorite($dbh, false);

                    $payload = json_encode(
                        [
                            "favorited" => null // TODO: false ???
                        ]
                    );
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }

                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/playlist/{id}', function (Request $request, Response $response, array $args) use ($container) {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }
                $params = $request->getParsedBody();
                if (! is_array($params)) {
                    throw new \Spieldose\Exception\InvalidParamsException();
                }

                if (! (array_key_exists("name", $params) && is_string($params["name"]))) {
                    throw new \Spieldose\Exception\InvalidParamsException("name");
                }
                if (!empty($args['id'])) {
                    $playlist = new \Spieldose\Entities\PlayList\PlayList(
                        $args['id'],
                        $params["name"]
                    );

                    $playlist->add($dbh, \Spieldose\UserSession::getUserId());
                    $payload = \Spieldose\Utils::getJSONPayload(
                        [
                            "playList" => $playlist,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/playlist/{id}/random_fill', function (Request $request, Response $response, array $args) use ($container) {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }
                if (!empty($args['id'])) {
                    $playlist = new \Spieldose\Entities\PlayList\PlayList(
                        $args['id'],
                        ""
                    );
                    $playlist->randomFill($dbh, 32, \Spieldose\UserSession::getUserId());
                    $playlist->get($dbh, \Spieldose\UserSession::getUserId());
                    $payload = \Spieldose\Utils::getJSONPayload(
                        [
                            "playList" => $playlist,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/playlist/{id}/empty', function (Request $request, Response $response, array $args) use ($container) {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }
                if (!empty($args['id'])) {
                    $playlist = new \Spieldose\Entities\PlayList\PlayList(
                        $args['id'],
                        ""
                    );
                    $playlist->empty($dbh, \Spieldose\UserSession::getUserId());
                    $playlist->get($dbh, \Spieldose\UserSession::getUserId());
                    $payload = \Spieldose\Utils::getJSONPayload(
                        [
                            "playList" => $playlist,
                        ]
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/playlist/{id}/close', function (Request $request, Response $response, array $args) use ($container) {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }
                if (!empty($args['id'])) {
                    $playlist = new \Spieldose\Entities\PlayList\PlayList(
                        $args['id'],
                        ""
                    );
                    $playlist->close($dbh, \Spieldose\UserSession::getUserId());
                    $payload = \Spieldose\Utils::getJSONPayload(
                        []
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->put('/current_playlist/{id}/current_item_index/{index}', function (Request $request, Response $response, array $args) use ($container) {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }
                if (empty($args['id'])) {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
                if (! is_numeric($args['index'])) {
                    throw new \Spieldose\Exception\InvalidParamsException('index');
                }

                $playlist = new \Spieldose\Entities\PlayList\PlayList(
                    $args['id'],
                    ""
                );
                $playlist->setCurrentItemIndex($dbh, intval($args["index"]), \Spieldose\UserSession::getUserId());
                $payload = \Spieldose\Utils::getJSONPayload(
                    []
                );
                $response->getBody()->write($payload);
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->delete('/playlist/{id}', function (Request $request, Response $response, array $args) use ($container) {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }
                if (!empty($args['id'])) {
                    $playlist = new \Spieldose\Entities\PlayList\PlayList(
                        $args['id'],
                        "",
                    );

                    $playlist->delete($dbh, \Spieldose\UserSession::getUserId());
                    $payload = \Spieldose\Utils::getJSONPayload(
                        []
                    );
                    $response->getBody()->write($payload);
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/current_playlists', function (Request $request, Response $response, array $args) use ($container) {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }
                $payload = json_encode(
                    [
                        "playLists" => \Spieldose\Entities\PlayList\PlayList::getCurrentPlayLists($dbh, \Spieldose\UserSession::getUserId()),
                    ],
                );
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                }

                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/current_playlist', function (Request $request, Response $response, array $args) use ($container) {
                $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
                if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
                    throw new \RuntimeException("Failed to create database handler from container");
                }

                $playlistItems = \Spieldose\Entities\File::getRandomPlayList($dbh, 32);
                $payload = json_encode(
                    [
                        "playList" => [
                            "items" => $playlistItems
                        ],
                    ]
                );
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                }

                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            /*
            $group->group('/user', function (RouteCollectorProxy $group) use ($app) {
                $group->get('/profile', function (Request $request, Response $response, array $args) use ($app) {
                    $user = new \Spieldose\User(\Spieldose\UserSession::getUserId());
                    $user->get($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));
                    unset($user->password);
                    unset($user->passwordHash);
                    $payload = json_encode(
                        [
                            'data' => $user
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->put('/profile', function (Request $request, Response $response, array $args) use ($app) {
                    $params = $request->getParsedBody();
                    $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
                    $user = new \Spieldose\User(\Spieldose\UserSession::getUserId());
                    $user->get($dbh);
                    if ($params["email"] != \Spieldose\UserSession::getEmail()) {
                        $tmpUser = new \Spieldose\User(
                            "",
                            $params["email"]
                        );
                        if ($tmpUser->exists($dbh)) {
                            throw new \Spieldose\Exception\AlreadyExistsException("email");
                        }
                    }
                    $user->email = $params["email"] ?? "";
                    $user->password = $params["password"] ?? "";
                    $user->update($dbh);
                    unset($user->password);
                    unset($user->passwordHash);
                    $payload = json_encode(
                        [
                            'data' => $user
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/global_search', function (Request $request, Response $response, array $args) use ($app) {
                $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
                $params = $request->getParsedBody();
                $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                    [
                        new \aportela\DatabaseBrowserWrapper\SortItem(
                            (isset($params["sort"]) && isset($params["sort"]["field"]) && !empty($params["sort"]["field"])) ? $params["sort"]["field"] : "title",
                            (isset($params["sort"]) && isset($params["sort"]["order"]) && $params["sort"]["order"] == "DESC") ? \aportela\DatabaseBrowserWrapper\Order::DESC : \aportela\DatabaseBrowserWrapper\Order::ASC,
                            true
                        )
                    ]
                );
                $pager = new \aportela\DatabaseBrowserWrapper\Pager(true, $params["pager"]["currentPageIndex"] ?? 1, $params["pager"]["resultsPage"] ?? 3);
                $data = array();
                $filter = array(
                    "title" => $params["filter"]["text"] ?? "",
                );
                $result = \Spieldose\Entities\Track::search(
                    $dbh,
                    $filter,
                    $sort,
                    $pager
                );
                $data["tracks"] = $result->items;
                $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                    [
                        new \aportela\DatabaseBrowserWrapper\SortItem(
                            (isset($params["sort"]) && isset($params["sort"]["field"]) && !empty($params["sort"]["field"])) ? $params["sort"]["field"] : "name",
                            (isset($params["sort"]) && isset($params["sort"]["order"]) && $params["sort"]["order"] == "DESC") ? \aportela\DatabaseBrowserWrapper\Order::DESC : \aportela\DatabaseBrowserWrapper\Order::ASC,
                            true
                        )
                    ]
                );
                $filter = new \aportela\DatabaseBrowserWrapper\Filter(
                    array(
                        "name" => $params["filter"]["text"] ?? "",
                    )
                );
                $result = \Spieldose\Entities\Artist::search($dbh, $filter, $sort, $pager);
                $data["artists"] = $result->items;
                $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                    [
                        new \aportela\DatabaseBrowserWrapper\SortItem(
                            (isset($params["sort"]) && isset($params["sort"]["field"]) && !empty($params["sort"]["field"])) ? $params["sort"]["field"] : "title",
                            (isset($params["sort"]) && isset($params["sort"]["order"]) && $params["sort"]["order"] == "DESC") ? \aportela\DatabaseBrowserWrapper\Order::DESC : \aportela\DatabaseBrowserWrapper\Order::ASC,
                            true
                        )
                    ]
                );
                $filter = array(
                    "title" => $params["filter"]["text"] ?? "",
                );
                $result = \Spieldose\Entities\Album::search($dbh, $filter, $sort, $pager, true);
                $data["albums"] = $result->items;
                $payload = json_encode(
                    [
                        "data" => $data
                    ]
                );
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->group('/track', function (RouteCollectorProxy $group) use ($app) {
                $group->get('/{id}', function (Request $request, Response $response, array $args) use ($app) {
                    if (!empty($args['id'])) {
                        $track = new \Spieldose\Entities\Track($args['id']);
                        $track->get($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));
                        $payload = json_encode(
                            [
                                "track" => $track
                            ]
                        );
                        if (json_last_error() != JSON_ERROR_NONE) {
                            throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                        }
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                    } else {
                        throw new \Spieldose\Exception\InvalidParamsException('id');
                    }
                });

                // TODO: move to /search/tracks
                $group->post('/search', function (Request $request, Response $response, array $args) use ($app) {
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
                    $data = \Spieldose\Entities\Track::search(
                        $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class),
                        $filter,
                        $sort,
                        $pager
                    );
                    $payload = json_encode(
                        [
                            "data" => $data
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->get('/increase_play_count/{id}', function (Request $request, Response $response, array $args) use ($app) {
                    $track = new \Spieldose\Entities\Track($args["id"]);
                    $track->increasePlayCount($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));
                    $payload = json_encode(
                        [
                            // TODO: success: true ?
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->get('/set_favorite/{id}', function (Request $request, Response $response, array $args) use ($app) {
                    $track = new \Spieldose\Entities\Track($args["id"]);
                    $track->toggleFavorite($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class), true);
                    $payload = json_encode(
                        [
                            "favorited" => $track->favorited
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->get('/unset_favorite/{id}', function (Request $request, Response $response, array $args) use ($app) {
                    $track = new \Spieldose\Entities\Track($args["id"]);
                    $track->toggleFavorite($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class), false);
                    $payload = json_encode(
                        [
                            "favorited" => null // TODO: false ???
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
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
                            ->withHeader('Content-Length', (string) $filesize)
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
                    $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
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
                            ->withHeader('Content-Length', (string) $filesize)
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
                $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
                $logger = $this->get(\Spieldose\Logger\ThumbnailLogger::class);
                $localPathNormalSize = null;
                try {
                    $localPathNormalSize = \Spieldose\Entities\Track::getLocalThumbnail($dbh, $logger, $args['id'], $settings['sizes']['normal']['quality'], $settings['sizes']['normal']['width'], $settings['sizes']['normal']['height']);
                } catch (\Spieldose\Exception\NotFoundException $e) {
                }
                if (empty($localPathNormalSize)) {
                    try {
                        $localPathNormalSize = \Spieldose\Entities\Track::getRemoteThumbnail($dbh, $logger, $args['id'], $settings['sizes']['normal']['quality'], $settings['sizes']['normal']['width'], $settings['sizes']['normal']['height']);
                    } catch (\Spieldose\Exception\NotFoundException $e) {
                    }
                }
                $localPathSmallSize = null;
                try {
                    $localPathSmallSize = \Spieldose\Entities\Track::getLocalThumbnail($dbh, $logger, $args['id'], $settings['sizes']['small']['quality'], $settings['sizes']['small']['width'], $settings['sizes']['small']['height']);
                } catch (\Spieldose\Exception\NotFoundException $e) {
                }
                if (empty($localPathSmallSize)) {
                    try {
                        $localPathSmallSize = \Spieldose\Entities\Track::getRemoteThumbnail($dbh, $logger, $args['id'], $settings['sizes']['small']['quality'], $settings['sizes']['small']['width'], $settings['sizes']['small']['height']);
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
                        ->withHeader('Content-Length', (string) $filesize)
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
                        ->withHeader('Content-Length', (string) $filesize)
                        ->withHeader('ETag', sha1($args["size"] . $args['hash'] . $localPath . $filesize))
                        ->withHeader('Cache-Control', 'max-age=86400')
                        ->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\NotFoundException('Invalid / empty path for hash: ' . $args['hash']);
                }
            });

            */


            /*
            $group->get('/artist_overview', function (Request $request, Response $response, array $args) use ($app) {
                $queryParams = $request->getQueryParams();
                $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
                // TODO: change dbh handler to public methods param ?
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
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("mbId,name");
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/artist', function (Request $request, Response $response, array $args) use ($app) {
                $queryParams = $request->getQueryParams();
                $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
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
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("mbId,name");
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/artists_genres', function (Request $request, Response $response, array $args) use ($app) {
                $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
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
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/album/search', function (Request $request, Response $response, array $args) use ($app) {
                $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
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
                $payload = json_encode(
                    [
                        "data" => $data
                    ]
                );
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/album', function (Request $request, Response $response, array $args) use ($app) {
                $queryParams = $request->getQueryParams();
                $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
                $album = new \Spieldose\Entities\Album(
                    $queryParams["mbId"] ?? null,
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
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/album/small_random_covers/{count:[0-9]+}', function (Request $request, Response $response, array $args) use ($app) {
                // TODO
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
                        fn($hash) =>
                        sprintf(\Spieldose\API::CACHED_HASH_SMALL_THUMBNAIL, $hash),
                        $hashes
                    );
                }
                $payload = json_encode(
                    [
                        'coverURLs' => $urls
                    ]
                );
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                }
                $response->getBody()->write($payload);
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

            $group->get('/path/tree', function (Request $request, Response $response, array $args) use ($app) {
                $data = \Spieldose\Path::getTree($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));
                $payload = json_encode(
                    [
                        "items" => $data
                    ]
                );
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->group('/metrics', function (RouteCollectorProxy $group) use ($app) {
                $group->post('/tracks', function (Request $request, Response $response, array $args) use ($app) {
                    $params = $request->getParsedBody();
                    $filter = $params["filter"] ?? [];
                    $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                        [
                            new \aportela\DatabaseBrowserWrapper\SortItem($params["sortField"], \aportela\DatabaseBrowserWrapper\Order::DESC, false)
                        ]
                    );
                    $pager = new \aportela\DatabaseBrowserWrapper\Pager(false, 1, $params["count"] ?? 5);
                    $data = \Spieldose\Metrics::searchTracks($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class), $filter, $sort, $pager);
                    $payload = json_encode(
                        [
                            "data" => $data
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->post('/artists', function (Request $request, Response $response, array $args) use ($app) {
                    $params = $request->getParsedBody();
                    $filter = $params["filter"] ?? [];
                    $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                        [
                            new \aportela\DatabaseBrowserWrapper\SortItem($params["sortField"], \aportela\DatabaseBrowserWrapper\Order::DESC, false)
                        ]
                    );
                    $pager = new \aportela\DatabaseBrowserWrapper\Pager(false, 1, $params["count"] ?? 5);
                    $data = \Spieldose\Metrics::searchArtists($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class), $filter, $sort, $pager);
                    $payload = json_encode(
                        [
                            "data" => $data
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->post('/albums', function (Request $request, Response $response, array $args) use ($app) {
                    $params = $request->getParsedBody();
                    $filter = $params["filter"] ?? [];
                    $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                        [
                            new \aportela\DatabaseBrowserWrapper\SortItem($params["sortField"], \aportela\DatabaseBrowserWrapper\Order::DESC, false)
                        ]
                    );
                    $pager = new \aportela\DatabaseBrowserWrapper\Pager(false, 1, $params["count"] ?? 5);
                    $data = \Spieldose\Metrics::searchAlbums($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class), $filter, $sort, $pager);
                    $payload = json_encode(
                        [
                            "data" => $data
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->post('/genres', function (Request $request, Response $response, array $args) use ($app) {
                    $params = $request->getParsedBody();
                    $filter = $params["filter"] ?? [];
                    $sort = new \aportela\DatabaseBrowserWrapper\Sort(
                        [
                            new \aportela\DatabaseBrowserWrapper\SortItem($params["sortField"], \aportela\DatabaseBrowserWrapper\Order::DESC, false)
                        ]
                    );
                    $pager = new \aportela\DatabaseBrowserWrapper\Pager(false, 1, $params["count"] ?? 5);
                    $data = \Spieldose\Metrics::searchGenres($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class), $filter, $sort, $pager);
                    $payload = json_encode(
                        [
                            "data" => $data
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->post('/date_range', function (Request $request, Response $response, array $args) use ($app) {
                    $params = $request->getParsedBody();
                    $filter = $params["filter"] ?? [];
                    $data = \Spieldose\Metrics::searchPlaysByDateRange($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class), $filter);
                    $payload = json_encode(
                        [
                            "data" => $data
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->post('/by_user', function (Request $request, Response $response, array $args) use ($app) {
                    $params = $request->getParsedBody();
                    $filter = $params["filter"] ?? [];
                    $data = \Spieldose\Metrics::searchPlaysByUser($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class), $filter);
                    $payload = json_encode(
                        [
                            "data" => $data
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/playlist/search', function (Request $request, Response $response, array $args) use ($app) {
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
                    $data = \Spieldose\Playlist::search($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class), $filter, $sort, $pager);
                    $payload = json_encode(
                        [
                            "data" => $data
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("filter");
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/playlist/add', function (Request $request, Response $response, array $args) use ($app) {
                $params = $request->getParsedBody();
                $playlist = new \Spieldose\Playlist(
                    $params["playlist"]["id"] ?? "",
                    $params["playlist"]["name"] ?? "",
                    $params["playlist"]["tracks"] ?? [],
                    $params["playlist"]["public"] ?? false
                );
                $playlist->add($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));
                $payload = json_encode(
                    [
                        "playlist" => $params["playlist"]
                    ]
                );
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/playlist/update', function (Request $request, Response $response, array $args) use ($app) {
                $params = $request->getParsedBody();
                $playlist = new \Spieldose\Playlist(
                    $params["playlist"]["id"] ?? "",
                    $params["playlist"]["name"] ?? "",
                    $params["playlist"]["tracks"] ?? [],
                    $params["playlist"]["public"] ?? false
                );
                $playlist->update($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));
                $payload = json_encode(
                    [
                        "playlist" => $params["playlist"]
                    ]
                );
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->delete('/playlist/{id}', function (Request $request, Response $response, array $args) use ($app) {
                if (!empty($args['id'])) {
                    $playlist = new \Spieldose\Playlist(
                        $args['id'],
                        "",
                        [],
                        false
                    );
                    $playlist->remove($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));
                    $payload = json_encode(
                        [
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/playlist/{id}', function (Request $request, Response $response, array $args) use ($app) {
                if (!empty($args['id'])) {
                    $playlist = new \Spieldose\Playlist(
                        $args['id'],
                        "",
                        [],
                        false
                    );
                    $playlist->get($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));
                    $payload = json_encode(
                        [
                            "playlist" => $playlist
                        ]
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException('id');
                }
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/current_playlist', function (Request $request, Response $response, array $args) use ($app) {
                $currentPlaylist = new \Spieldose\CurrentPlaylist();
                $currentPlaylist->get($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class));
                // TODO: initialState
                $payload = json_encode($currentPlaylist);
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->group('/current_playlist', function (RouteCollectorProxy $group) use ($app) {
                $group->get('/sort/random', function (Request $request, Response $response, array $args) use ($app) {
                    $queryParams = $request->getQueryParams();
                    $currentPlaylist = new \Spieldose\CurrentPlaylist();
                    // TODO: initialState
                    $payload = json_encode($currentPlaylist->randomSort($app->getContainer()->get(\aportela\DatabaseWrapper\DB::class), isset($queryParams["shuffle"]) && $queryParams["shuffle"] == "true"));
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->post('/sort/indexes', function (Request $request, Response $response, array $args) use ($app) {
                    $params = $request->getParsedBody();
                    $currentPlaylist = new \Spieldose\CurrentPlaylist();
                    $indexes = [];
                    if (isset($params["indexes"]) && is_array($params["indexes"])) {
                        $indexes = $params["indexes"];
                    }
                    $payload = json_encode(
                        $currentPlaylist->sortByIndexes(
                            $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class),
                            $indexes,
                            isset($params["shuffle"]) && $params["shuffle"] == "true"
                        )
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->get('/current_element', function (Request $request, Response $response, array $args) use ($app) {
                    $queryParams = $request->getQueryParams();
                    $currentPlaylist = new \Spieldose\CurrentPlaylist();
                    $payload = json_encode(
                        $currentPlaylist->getCurrentElement(
                            $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class),
                            isset($queryParams["shuffle"]) && $queryParams["shuffle"] == "true"
                        )
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->get('/previous_element', function (Request $request, Response $response, array $args) use ($app) {
                    $queryParams = $request->getQueryParams();
                    $currentPlaylist = new \Spieldose\CurrentPlaylist();
                    $payload = json_encode(
                        $currentPlaylist->getPreviousElement(
                            $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class),
                            isset($queryParams["shuffle"]) && $queryParams["shuffle"] == "true"
                        )
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->get('/next_element', function (Request $request, Response $response, array $args) use ($app) {
                    $queryParams = $request->getQueryParams();
                    $currentPlaylist = new \Spieldose\CurrentPlaylist();
                    $payload = json_encode(
                        $currentPlaylist->getNextElement(
                            $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class),
                            isset($queryParams["shuffle"]) && $queryParams["shuffle"] == "true"
                        )
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->get('/element_at_index', function (Request $request, Response $response, array $args) use ($app) {
                    $queryParams = $request->getQueryParams();
                    $currentPlaylist = new \Spieldose\CurrentPlaylist();
                    $payload = json_encode(
                        $currentPlaylist->getElementAtIndex(
                            $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class),
                            $queryParams["index"] ?? -1
                        )
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->post('/remove_element_at_index', function (Request $request, Response $response, array $args) use ($app) {
                    $params = $request->getParsedBody();
                    $currentPlaylist = new \Spieldose\CurrentPlaylist();
                    $payload = json_encode(
                        $currentPlaylist->removeElementAtIndex(
                            $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class),
                            $params["index"] ?? -1,
                            ((isset($params["shuffle"]) && $params["shuffle"] == "true") && $params["shuffle"])
                        )
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->post('/discover_tracks', function (Request $request, Response $response, array $args) use ($app) {
                    $params = $request->getParsedBody();
                    $currentPlaylist = new \Spieldose\CurrentPlaylist();
                    $payload = json_encode(
                        $currentPlaylist->discover(
                            $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class),
                            $params["count"] ?? 32,
                            ((isset($params["shuffle"]) && $params["shuffle"] == "true") && $params["shuffle"])
                        )
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->post('/set_tracks', function (Request $request, Response $response, array $args) use ($app) {
                    $params = $request->getParsedBody();
                    $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
                    $currentPlaylist = new \Spieldose\CurrentPlaylist();
                    $currentPlaylist->playlist->id = null;
                    if (isset($params["trackIds"]) && is_array($params["trackIds"])) {
                        if (!$currentPlaylist->save($dbh, $params["trackIds"])) {
                            // TODO
                            throw new \Exception("save error");
                        }
                    } else if (isset($params["album"])) {
                        $albumTrackIds = \Spieldose\Entities\Album::getTrackIds(
                            $dbh,
                            [
                                "mbId" => $params["album"]["mbId"] ?? null,
                                "title" => $params["album"]["title"] ?? null,
                                "artistName" => $params["album"]["artist"]["name"] ?? null,
                                "year" => $params["album"]["year"] ?? null
                            ]
                        );
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
                    $payload = json_encode(
                        $currentPlaylist->getCurrentElement(
                            $dbh,
                            (isset($params["shuffle"]) && $params["shuffle"] == "true")
                        )
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->post('/append_tracks', function (Request $request, Response $response, array $args) use ($app) {
                    $params = $request->getParsedBody();
                    $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
                    $currentPlaylist = new \Spieldose\CurrentPlaylist();
                    if (isset($params["trackIds"]) && is_array($params["trackIds"])) {
                        if (!$currentPlaylist->append($dbh, $params["trackIds"])) {
                            // TODO
                            throw new \Exception("save error");
                        }
                    } else if (isset($params["album"]) && isset($params["album"]["mbId"]) && !empty($params["album"]["mbId"])) {
                        $albumTrackIds = \Spieldose\Entities\Album::getTrackIds(
                            $dbh,
                            [
                                "mbId" => $params["album"]["mbId"] ?? null,
                                "title" => $params["album"]["title"] ?? null,
                                "artistName" => $params["album"]["artist"]["name"] ?? null,
                                "year" => $params["album"]["year"] ?? null
                            ]
                        );
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
                    $payload = json_encode(
                        $currentPlaylist->getCurrentElement(
                            $dbh,
                            (isset($params["shuffle"]) && $params["shuffle"] == "true")
                        )
                    );
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                    }
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                });

                $group->post('/set_radiostation', function (Request $request, Response $response, array $args) use ($app) {
                    $params = $request->getParsedBody();
                    if (!empty($params["id"])) {
                        $dbh =  $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
                        $currentPlaylist = new \Spieldose\CurrentPlaylist();
                        $currentPlaylist->setRadiostation($dbh, $params["id"]);
                        $payload = json_encode(
                            $currentPlaylist->getCurrentElement(
                                $dbh,
                                (isset($params["shuffle"]) && $params["shuffle"] == "true")
                            )
                        );
                        if (json_last_error() != JSON_ERROR_NONE) {
                            throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                        }
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                    } else {
                        throw new \Spieldose\Exception\InvalidParamsException("id");
                    }
                });
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->post('/radio_station/search', function (Request $request, Response $response, array $args) use ($app) {
                $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
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
                $payload = json_encode(
                    [
                        "data" => $data
                    ]
                );
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            $group->get('/lyrics', function (Request $request, Response $response, array $args) use ($app) {
                $queryParams = $request->getQueryParams();
                $dbh = $app->getContainer()->get(\aportela\DatabaseWrapper\DB::class);
                $title = $queryParams["title"] ?? "";
                $artist = $queryParams["artist"] ?? "";
                $lyrics = new \Spieldose\Lyrics($this->get(\Spieldose\Logger\ScraperLogger::class));
                $payload = json_encode(
                    [
                        'lyrics' => $lyrics->get($dbh, $title, $artist) ? $lyrics->lyrics : null
                    ]
                );
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \Spieldose\Exception\JSONSerializerException(json_last_error_msg());
                }
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            })->add(\Spieldose\Middleware\CheckAuth::class);

            */
        }
    )->add(\Spieldose\Middleware\APIExceptionCatcher::class);
};

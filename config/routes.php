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
        //$v = new \Spieldose\Database\Version(new \Spieldose\Database\DB($this), $this->get('settings')['database']['type']);

        return $this->get('Twig')->render($response, 'index.html.twig', [
            //'settings' => $this->get('settings'),
            //'settings' => $settings["twigParams"],
            //"locale" => $settings['common']['locale'],
            'initialState' => json_encode(
                array(
                    "logged" => \Spieldose\User::isLogged(),
                    "sessionExpireMinutes" => session_cache_expire(),
                    //'upgradeAvailable' => $v->hasUpgradeAvailable(),
                    //"defaultResultsPage" => $this->get('settings')['common']['defaultResultsPage'],
                    //"allowSignUp" => $this->get('settings')['common']['allowSignUp'],
                    //"liveSearch" => $this->get('settings')['common']['liveSearch'],
                    //"locale" => $this->get('settings')['common']['locale']
                )
            )
        ]);
    });

    $app->group("/api", function (Slim\Routing\RouteCollectorProxy $group) {

        /* user */

        $group->get('/user/poll', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
            $payload = json_encode(['sessionId' => session_id() ]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });

        $group->post('/user/signin', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
            $params = $request->getParsedBody();
            $u = new \Spieldose\User("", $params["email"] ?? "", $params["password"] ?? "");
            $dbh =  new \Spieldose\Database\DB(
                $this->get(PDO::class)
            );
            if ($u->login($dbh)) {
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
                $dbh =  new \Spieldose\Database\DB(
                    $this->get(PDO::class)
                );
                $u = new \Spieldose\User(
                    "",
                    $request->getParam("email", ""),
                    $request->getParam("password", "")
                );
                $exists = false;
                try {
                    $u->get($dbh);
                    $exists = true;
                } catch (\Spieldose\Exception\NotFoundException $e) {
                }
                if ($exists) {
                    $payload = json_encode([]);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
                } else {
                    $u->id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
                    $u->add($dbh);
                    $payload = json_encode([]);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
                }
            } else {
                throw new \Spieldose\Exception\AccessDeniedException("");
            }
        });

        $group->get('/user/signout', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
            \Spieldose\User::logout();
            $payload = json_encode(['logged' => false]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });

        /* user */

        $group->get('/thumbnail', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
            $params = $request->getQueryParams();
            $url = $params["url"] ?? "";
            $hash = $params["hash"] ?? "";
            if (! empty($url)) {
                $file = \Spieldose\Thumbnail::getCachedLocalPathFromUrl($url);
                if (! empty($file) && file_exists($file)) {
                    $filesize = filesize($file);
                    $f = fopen($file, 'r');
                    fseek($f, 0);
                    $data = fread($f, $filesize);
                    fclose($f);
                    $response->getBody()->write($data);
                    return $response
                    ->withHeader('Content-Type', "image/jpeg")
                    //->withHeader('Content-Length', $filesize)
                    ->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\NotFoundException("url");
                }
            } else if (! empty($hash)) {
                $file = \Spieldose\Thumbnail::getCachedLocalPathFromHash(new \Spieldose\Database\DB($this->get(PDO::class)), $hash);
                if (! empty($file) && file_exists($file)) {
                    $filesize = filesize($file);
                    $f = fopen($file, 'r');
                    fseek($f, 0);
                    $data = fread($f, $filesize);
                    fclose($f);
                    $response->getBody()->write($data);
                    return $response
                    ->withHeader('Content-Type', "image/jpeg")
                    //->withHeader('Content-Length', $filesize)
                    ->withStatus(200);
                } else {
                    throw new \Spieldose\Exception\NotFoundException("hash");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("url|hash");
            }
        });

        $group->post('/random_album_covers', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
            $params = $request->getParsedBody();
            $count = $params["count"] ?? 128;
            $data = \Spieldose\Album::getRandomAlbumCovers(
                new \Spieldose\Database\DB($this->get(PDO::class)),
                intval($count)
            );
            $response->getBody()->write(json_encode([
                'covers' => $data
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        });


        $group->group("", function(Slim\Routing\RouteCollectorProxy $group2) {

            /* track */

            $group2->get('/track/get/{id}', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $route = $request->getAttribute('route');
                $track  = new \Spieldose\Track($route->getArgument("id"));
                $db = new \Spieldose\Database\DB($this);
                $track->get($db);
                if (file_exists($track->path)) {
                    $track->incPlayCount($db);
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
                        return $response->withStatus(206)
                        ->withHeader('Content-Type', $track->mime ? $track->mime: "application/octet-stream")
                        ->withHeader('Content-Disposition', 'attachment; filename="' . basename($track->path) . '"')
                        ->withHeader('Content-Length', $filesize)
                        ->withHeader('Content-Range', 'bytes ' . $offset . '-' . ($offset + $length - 1) . '/' . $filesize)
                        ->withHeader('Accept-Ranges', 'bytes')
                        ->write($data);
                    } else {
                        return $response->withStatus(200)
                            ->withHeader('Content-Type', $track->mime ? $track->mime: "application/octet-stream")
                            ->withHeader('Content-Disposition', 'attachment; filename="' . basename($track->path) . '"')
                            ->withHeader('Content-Length', $filesize)
                            ->withHeader('Accept-Ranges', 'bytes')
                            ->write($data);
                    }
                } else {
                    throw new \Spieldose\Exception\NotFoundException("id");
                }
            });

            $group2->post('/track/{id}/love', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $route = $request->getAttribute('route');
                $track  = new \Spieldose\Track($route->getArgument("id"));
                $db = new \Spieldose\Database\DB($this);
                $loved = $track->love($db);
                return $response->withJson(['loved' => $loved ? "1": "0"], 200);
            });

            $group2->post('/track/{id}/unlove', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $route = $request->getAttribute('route');
                $track  = new \Spieldose\Track($route->getArgument("id"));
                $db = new \Spieldose\Database\DB($this);
                $loved = $track->unLove($db);
                return $response->withJson(['loved' => "0" ], 200);
            });

            $group2->post('/track/search', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $filter = array();
                $params = $request->getParsedBody();
                $data = \Spieldose\Track::search(
                    new \Spieldose\Database\DB($this),
                    intval($request->getParam("actualPage", 1)),
                    intval($request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage'])),
                    array(
                        "text" => $request->getParam("text", ""),
                        "artist" => $request->getParam("artist", ""),
                        "album" => $request->getParam("album", ""),
                        "year" => $request->getParam("year", ""),
                        "path" => $request->getParam("path", ""),
                        "loved" => $request->getParam("loved", "")
                    ),
                    $request->getParam("orderBy", "")
                );
                return $response->withJson(['tracks' => $data->results, 'totalResults' => $data->totalResults, 'actualPage' => $data->actualPage, 'resultsPage' => $data->resultsPage, 'totalPages' => $data->totalPages], 200);
            });

            /* track */

            /* artist */

            $group2->post('/artist/search', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $data = \Spieldose\Artist::search(
                    new \Spieldose\Database\DB($this),
                    $request->getParam("actualPage", 1),
                    $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                    array(
                        "partialName" => $request->getParam("partialName", ""),
                        "name" => $request->getParam("name", ""),
                        "withoutMbid" => $request->getParam("withoutMbid", false)
                    ),
                    $request->getParam("orderBy", "")
                );
                return $response->withJson(
                    [
                        'artists' => $data->results,
                        "pagination" => array(
                            'totalResults' => $data->totalResults,
                            'actualPage' => $data->actualPage,
                            'resultsPage' => $data->resultsPage,
                            'totalPages' => $data->totalPages
                        )
                    ],
                    200
                );
            });

            $group2->get('/artist/{name:.*}', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $route = $request->getAttribute('route');
                $artist = new \Spieldose\Artist($route->getArgument("name"));
                $artist->get(new \Spieldose\Database\DB($this));
                return $response->withJson(['artist' => $artist], 200);
            });

            $group2->put('/artist/{name:.*}/mbid', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $route = $request->getAttribute('route');
                $mbid = $request->getParam("mbid", "");
                if (! empty($mbid)) {
                    \Spieldose\Artist::overwriteMusicBrainz(
                        new \Spieldose\Database\DB($this),
                        $route->getArgument("name"),
                        $mbid
                    );
                } else {
                    \Spieldose\Artist::clearMusicBrainz(
                        new \Spieldose\Database\DB($this),
                        $route->getArgument("name")
                    );
                }
                return $response->withJson([], 200);
            });

            /* artist */

            /* album */

            $group2->post('/album/search', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $data = \Spieldose\Album::search(
                    new \Spieldose\Database\DB($this),
                    $request->getParam("actualPage", 1),
                    $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                    array(
                        "partialName" => $request->getParam("partialName", ""),
                        "name" => $request->getParam("name", ""),
                        "partialArtist" => $request->getParam("partialArtist", ""),
                        "artist" => $request->getParam("artist", ""),
                        "year" => $request->getParam("year", "")
                    ),
                    $request->getParam("orderBy", "")
                );
                return $response->withJson(
                    [
                        'albums' => $data->results,
                        "pagination" => array(
                            'totalResults' => $data->totalResults,
                            'actualPage' => $data->actualPage,
                            'resultsPage' => $data->resultsPage,
                            'totalPages' => $data->totalPages
                        )
                    ],
                    200
                );
            });

            /* album */

            /* path */

            $group2->post('/path/search', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $data = \Spieldose\Path::search(
                    new \Spieldose\Database\DB($this),
                    $request->getParam("actualPage", 1),
                    $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                    array(
                        "name" => $request->getParam("name", ""),
                        "partialName" => $request->getParam("partialName", "")
                    ),
                    $request->getParam("orderBy", "")
                );
                return $response->withJson(
                    [
                        'paths' => $data->results,
                        "pagination" => array(
                            'totalResults' => $data->totalResults,
                            'actualPage' => $data->actualPage,
                            'resultsPage' => $data->resultsPage,
                            'totalPages' => $data->totalPages
                        )
                    ],
                    200
                );
            });

            /* path */

            /* playlist */

            $group2->get('/playlist/{id}', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $route = $request->getAttribute('route');
                $playlist = new \Spieldose\Playlist($route->getArgument("id"), "", array());
                $dbh = new \Spieldose\Database\DB($this);
                if ($playlist->isAllowed($dbh)) {
                    $playlist->get($dbh);
                    return $response->withJson(['playlist' => $playlist], 200);
                } else {
                    throw new \Spieldose\Exception\AccessDeniedException("");
                }
            });

            $group2->post('/playlist/search', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $data = \Spieldose\Playlist::search(
                    new \Spieldose\Database\DB($this),
                    $request->getParam("actualPage", 1),
                    $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                    array(
                        "name" => $request->getParam("name", ""),
                        "partialName" => $request->getParam("partialName", "")
                    ),
                    $request->getParam("orderBy", "")
                );
                return $response->withJson(
                    [
                        'playlists' => $data->results,
                        "pagination" => array(
                            'totalResults' => $data->totalResults,
                            'actualPage' => $data->actualPage,
                            'resultsPage' => $data->resultsPage,
                            'totalPages' => $data->totalPages
                        )
                    ],
                    200
                );
            });

            $group2->post('/playlist/add', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
                $name = $request->getParam("name", "");
                $tracks = $request->getParam("tracks", array());
                $playlist = new \Spieldose\Playlist(
                    $id,
                    $name,
                    $tracks
                );
                $dbh = new \Spieldose\Database\DB($this);
                $playlist->add($dbh);
                return $response->withJson([ "playlist" => array("id" => $id, "name" => $name, "tracks" => $tracks) ], 200);
            });

            $group2->post('/playlist/update', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $id = $request->getParam("id", "");
                $name = $request->getParam("name", "");
                $tracks = $request->getParam("tracks", array());
                $playlist = new \Spieldose\Playlist(
                    $id,
                    $name,
                    $tracks
                );
                $dbh = new \Spieldose\Database\DB($this);
                if ($playlist->isAllowed($dbh)) {
                    $playlist->update($dbh);
                    return $response->withJson([ "playlist" => array("id" => $id, "name" => $name, "tracks" => $tracks) ], 200);
                } else {
                    throw new \Spieldose\Exception\AccessDeniedException("");
                }
            });

            $group2->post('/playlist/remove', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $id = $request->getParam("id", "");
                $playlist = new \Spieldose\Playlist(
                    $id,
                    "",
                    array()
                );
                $dbh = new \Spieldose\Database\DB($this);
                if ($playlist->isAllowed($dbh)) {
                    $playlist->remove($dbh);
                    return $response->withJson([ ], 200);
                } else {
                    throw new \Spieldose\Exception\AccessDeniedException("");
                }
            });

            /* playlist */

            /* radio stations */

            $group2->get('/radio_station/{id}', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $route = $request->getAttribute('route');
                $radioStation = new \Spieldose\RadioStation($route->getArgument("id"), "", "", 0, "");
                $dbh = new \Spieldose\Database\DB($this);
                if ($radioStation->isAllowed($dbh)) {
                    $radioStation->get($dbh);
                    return $response->withJson(['radioStation' => $radioStation], 200);
                } else {
                    throw new \Spieldose\Exception\AccessDeniedException("");
                }
            });

            $group2->post('/radio_station/search', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $data = \Spieldose\RadioStation::search(
                    new \Spieldose\Database\DB($this),
                    $request->getParam("actualPage", 1),
                    $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                    array(
                        "name" => $request->getParam("name", ""),
                        "partialName" => $request->getParam("partialName", "")
                    ),
                    $request->getParam("orderBy", "")
                );
                return $response->withJson(
                    [
                        'radioStations' => $data->results,
                        "pagination" => array(
                            'totalResults' => $data->totalResults,
                            'actualPage' => $data->actualPage,
                            'resultsPage' => $data->resultsPage,
                            'totalPages' => $data->totalPages
                        )
                    ],
                    200
                );
            });

            $group2->post('/radio_station/add', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
                $name = $request->getParam("name", "");
                $url = $request->getParam("url", "");
                $urlType = intval($request->getParam("urlType", 0));
                $image = $request->getParam("image", "");
                $radioStation = new \Spieldose\RadioStation(
                    $id,
                    $name,
                    $url,
                    $urlType,
                    $image
                );
                $dbh = new \Spieldose\Database\DB($this);
                $radioStation->add($dbh);
                return $response->withJson([ "radioStation" => array("id" => $id, "name" => $name, "url" => $url, "image" => $image) ], 200);
            });

            $group2->post('/radio_station/update', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $id = $request->getParam("id", "");
                $name = $request->getParam("name", "");
                $url = $request->getParam("url", "");
                $urlType = intval($request->getParam("urlType", 0));
                $image = $request->getParam("image", "");
                $radioStation = new \Spieldose\RadioStation(
                    $id,
                    $name,
                    $url,
                    $urlType,
                    $image
                );
                $dbh = new \Spieldose\Database\DB($this);
                if ($radioStation->isAllowed($dbh)) {
                    $radioStation->update($dbh);
                    return $response->withJson([ "radioStation" => array("id" => $id, "name" => $name, "url" => $url, "image" => $image) ], 200);
                } else {
                    throw new \Spieldose\Exception\AccessDeniedException("");
                }
            });

            $group2->post('/radio_station/remove', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $id = $request->getParam("id", "");
                $radioStation = new \Spieldose\RadioStation(
                    $id,
                    "",
                    "",
                    0,
                    ""
                );
                $dbh = new \Spieldose\Database\DB($this);
                if ($radioStation->isAllowed($dbh)) {
                    $radioStation->remove($dbh);
                    return $response->withJson([ ], 200);
                } else {
                    throw new \Spieldose\Exception\AccessDeniedException("");
                }
            });

            /* radio stations */

            /* global search */

            $group2->post('/search/global', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $artistData = \Spieldose\Artist::search(
                    new \Spieldose\Database\DB($this),
                    $request->getParam("actualPage", 1),
                    $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                    array(
                        "partialName" => $request->getParam("text", "")
                    ),
                    $request->getParam("orderBy", "")
                );
                $albumData = \Spieldose\Album::search(
                    new \Spieldose\Database\DB($this),
                    $request->getParam("actualPage", 1),
                    $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                    array(
                        "partialName" => $request->getParam("text", "")
                    ),
                    $request->getParam("orderBy", "")
                );
                $trackData = \Spieldose\Track::search(
                    new \Spieldose\Database\DB($this),
                    $request->getParam("actualPage", 1),
                    $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                    array(
                        "text" => $request->getParam("text", "")
                    ),
                    $request->getParam("orderBy", "")
                );
                $playlistData = \Spieldose\Playlist::search(
                    new \Spieldose\Database\DB($this),
                    $request->getParam("actualPage", 1),
                    $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                    array(
                        "partialName" => $request->getParam("text", "")
                    ),
                    $request->getParam("orderBy", "")
                );
                return $response->withJson(['artists' => $artistData->results, 'albums' => $albumData->results, 'tracks' => $trackData->results, 'playlists' => $playlistData->results], 200);
            });

            /* global search */

            /* metrics */

            $group2->post('/metrics/top_played_tracks', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $metrics = \Spieldose\Metrics::GetTopPlayedTracks(
                    new \Spieldose\Database\DB($this),
                    array(
                        "fromDate" => $request->getParam("fromDate", ""),
                        "toDate" => $request->getParam("toDate", ""),
                        "artist" => $request->getParam("artist", ""),
                    ),
                    $request->getParam("count", 5)
                );
                return $response->withJson(['metrics' => $metrics], 200);
            });

            $group2->post('/metrics/top_artists', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $metrics = \Spieldose\Metrics::GetTopArtists(
                    new \Spieldose\Database\DB($this),
                    array(
                        "fromDate" => $request->getParam("fromDate", ""),
                        "toDate" => $request->getParam("toDate", ""),
                    ),
                    $request->getParam("count", 5)
                );
                return $response->withJson(['metrics' => $metrics], 200);
            });

            $group2->post('/metrics/top_albums', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $metrics = \Spieldose\Metrics::GetTopAlbums(
                    new \Spieldose\Database\DB($this),
                    array(
                        "fromDate" => $request->getParam("fromDate", ""),
                        "toDate" => $request->getParam("toDate", ""),
                    ),
                    $request->getParam("count", 5)
                );
                return $response->withJson(['metrics' => $metrics], 200);
            });

            $group2->post('/metrics/top_genres', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $metrics = \Spieldose\Metrics::GetTopGenres(
                    new \Spieldose\Database\DB($this),
                    array(
                        "fromDate" => $request->getParam("fromDate", ""),
                        "toDate" => $request->getParam("toDate", ""),
                    ),
                    $request->getParam("count", 5)
                );
                return $response->withJson(['metrics' => $metrics], 200);
            });

            $group2->post('/metrics/recently_added', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $entity = $request->getParam("entity", "");
                if (! empty($entity)) {
                    switch($entity) {
                        case "tracks":
                            $metrics = \Spieldose\Metrics::GetRecentlyAddedTracks(
                                new \Spieldose\Database\DB($this),
                                array(
                                ),
                                $request->getParam("count", 5)
                            );
                        break;
                        case "artists":
                            $metrics = \Spieldose\Metrics::GetRecentlyAddedArtists(
                                new \Spieldose\Database\DB($this),
                                array(
                                ),
                                $request->getParam("count", 5)
                            );
                        break;
                        case "albums":
                            $metrics = \Spieldose\Metrics::GetRecentlyAddedAlbums(
                                new \Spieldose\Database\DB($this),
                                array(
                                ),
                                $request->getParam("count", 5)
                            );

                        break;
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("entity");
                }
                return $response->withJson(['metrics' => $metrics], 200);
            });

            $group2->post('/metrics/recently_played', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $entity = $request->getParam("entity", "");
                if (! empty($entity)) {
                    switch($entity) {
                        case "tracks":
                            $metrics = \Spieldose\Metrics::GetRecentlyPlayedTracks(
                                new \Spieldose\Database\DB($this),
                                array(
                                ),
                                $request->getParam("count", 5)
                            );
                        break;
                        case "artists":
                            $metrics = \Spieldose\Metrics::GetRecentlyPlayedArtists(
                                new \Spieldose\Database\DB($this),
                                array(
                                ),
                                $request->getParam("count", 5)
                            );
                        break;
                        case "albums":
                            $metrics = \Spieldose\Metrics::GetRecentlyPlayedAlbums(
                                new \Spieldose\Database\DB($this),
                                array(
                                ),
                                $request->getParam("count", 5)
                            );
                        break;
                    }

                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("entity");
                }
                return $response->withJson(['metrics' => $metrics], 200);
            });

            $group2->post('/metrics/play_stats_by_hour', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $params = $request->getParsedBody();
                $metrics = \Spieldose\Metrics::GetPlayStatsByHour(
                    new \Spieldose\Database\DB($this),
                    array(
                    )
                );
                return $response->withJson(['metrics' => $metrics], 200);
            });

            $group2->post('/metrics/play_stats_by_weekday', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $metrics = \Spieldose\Metrics::GetPlayStatsByWeekDay(
                    new \Spieldose\Database\DB($this),
                    array(
                    )
                );
                return $response->withJson(['metrics' => $metrics], 200);
            });

            $group2->post('/metrics/play_stats_by_month', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $metrics = \Spieldose\Metrics::GetPlayStatsByMonth(
                    new \Spieldose\Database\DB($this),
                    array(
                    )
                );
                return $response->withJson(['metrics' => $metrics], 200);
            });

            $group2->post('/metrics/play_stats_by_year', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
                $metrics = \Spieldose\Metrics::GetPlayStatsByYear(
                    new \Spieldose\Database\DB($this),
                    array(
                    )
                );
                return $response->withJson(['metrics' => $metrics], 200);
            });

            /* metrics */

        });

    });



};

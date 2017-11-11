<?php
    declare(strict_types=1);

    use Slim\Http\Request;
    use Slim\Http\Response;

    $app->get('/', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton GET '/' route");
        return $this->view->render($response, 'index.html.twig', array(
            'settings' => $this->settings["twigParams"]
        ));
    });

    $app->get('/api/user/poll', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton GET '/api/user/poll' route");
        if (\Spieldose\User::isLogged()) {
            return $response->withJson(['success' => true], 200);
        } else {
            return $response->withJson(['success' => false], 403);
        }
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/user/signin', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/user/signin' route");
        $u = new \Spieldose\User($request->getParam("email"));
        if ($u->login(new \Spieldose\Database\DB(), $request->getParam("password"))) {
            return $response->withJson(['logged' => true], 200);
        } else {
            return $response->withJson(['logged' => false], 401);
        }
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->get('/api/user/signout', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton GET '/api/user/signout' route");
        \Spieldose\User::logout();
        return $response->withJson(['logged' => false], 200);
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->get('/api/track/get/{id}', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton GET '/api/track/get' route");
        $route = $request->getAttribute('route');
        $track  = new \Spieldose\Track($route->getArgument("id"));
        $db = new \Spieldose\Database\DB();
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
            throw new \Spieldose\NotFoundException("id");
        }
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/track/{id}/love', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton GET '/api/track/love' route");
        $route = $request->getAttribute('route');
        $track  = new \Spieldose\Track($route->getArgument("id"));
        $db = new \Spieldose\Database\DB();
        $loved = $track->love($db);
        return $response->withJson(['loved' => $loved ? "1": "0"], 200);
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/track/{id}/unlove', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton GET '/api/track/unlove' route");
        $route = $request->getAttribute('route');
        $track  = new \Spieldose\Track($route->getArgument("id"));
        $db = new \Spieldose\Database\DB();
        $loved = $track->unLove($db);
        return $response->withJson(['loved' => "0" ], 200);
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/track/search', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/track/search' route");
        $filter = array();
        $data = \Spieldose\Track::search(
            new \Spieldose\Database\DB(),
            $request->getParam("actualPage", 1),
            $request->getParam("resultsPage", DEFAULT_RESULTS_PAGE),
            array(
                "text" => $request->getParam("text", ""),
                "artist" => $request->getParam("artist", ""),
                "album" => $request->getParam("album", "")
            ),
            $request->getParam("orderBy", "")
        );
        return $response->withJson(['tracks' => $data->results, 'totalResults' => $data->totalResults, 'actualPage' => $data->actualPage, 'resultsPage' => $data->resultsPage, 'totalPages' => $data->totalPages], 200);
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/artist/search', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/artist/search' route");
        $data = \Spieldose\Artist::search(
            new \Spieldose\Database\DB(),
            $request->getParam("actualPage", 1),
            $request->getParam("resultsPage", DEFAULT_RESULTS_PAGE),
            array(
                "text" => $request->getParam("text", "")
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
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->get('/api/artist/{name}', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton GET '/api/artist/' route");
        $route = $request->getAttribute('route');
        $artist = new \Spieldose\Artist($route->getArgument("name"));
        $artist->get(new \Spieldose\Database\DB());
        return $response->withJson(['artist' => $artist], 200);
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/album/search', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/album/search' route");
        $data = \Spieldose\Album::search(
            new \Spieldose\Database\DB(),
            $request->getParam("actualPage", 1),
            $request->getParam("resultsPage", DEFAULT_RESULTS_PAGE),
            array(
                "text" => $request->getParam("text", "")
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
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/search/global', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/search/global' route");
        $artistData = \Spieldose\Artist::search(
            new \Spieldose\Database\DB(),
            $request->getParam("actualPage", 1),
            $request->getParam("resultsPage", DEFAULT_RESULTS_PAGE),
            array(
                "text" => $request->getParam("text", "")
            ),
            $request->getParam("orderBy", "")
        );
        $albumData = \Spieldose\Album::search(
            new \Spieldose\Database\DB(),
            $request->getParam("actualPage", 1),
            $request->getParam("resultsPage", DEFAULT_RESULTS_PAGE),
            array(
                "text" => $request->getParam("text", "")
            ),
            $request->getParam("orderBy", "")
        );
        $trackData = \Spieldose\Track::search(
            new \Spieldose\Database\DB(),
            $request->getParam("actualPage", 1),
            $request->getParam("resultsPage", DEFAULT_RESULTS_PAGE),
            array(
                "text" => $request->getParam("text", "")
            ),
            $request->getParam("orderBy", "")
        );
        return $response->withJson(['artists' => $artistData->results, 'albums' => $albumData->results, 'tracks' => $trackData->results ], 200);
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/metrics/top_played_tracks', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/metrics/top_played_tracks' route");
        $metrics = \Spieldose\Metrics::GetTopPlayedTracks(
            new \Spieldose\Database\DB(),
            array(
                "fromDate" => $request->getParam("fromDate", ""),
                "toDate" => $request->getParam("toDate", ""),
                "artist" => $request->getParam("artist", ""),
            ),
            $request->getParam("count", 5)
        );
        return $response->withJson(['metrics' => $metrics], 200);
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/metrics/top_artists', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/metrics/top_artists' route");
        $metrics = \Spieldose\Metrics::GetTopArtists(
            new \Spieldose\Database\DB(),
            array(
                "fromDate" => $request->getParam("fromDate", ""),
                "toDate" => $request->getParam("toDate", ""),
            ),
            $request->getParam("count", 5)
        );
        return $response->withJson(['metrics' => $metrics], 200);
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/metrics/top_genres', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/metrics/top_genres' route");
        $metrics = \Spieldose\Metrics::GetTopGenres(
            new \Spieldose\Database\DB(),
            array(
                "fromDate" => $request->getParam("fromDate", ""),
                "toDate" => $request->getParam("toDate", ""),
            ),
            $request->getParam("count", 5)
        );
        return $response->withJson(['metrics' => $metrics], 200);
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/metrics/recently_added', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/metrics/recently_added' route");
        $entity = $request->getParam("entity", "");
        if (! empty($entity)) {
            switch($entity) {
                case "tracks":
                    $metrics = \Spieldose\Metrics::GetRecentlyAddedTracks(
                        new \Spieldose\Database\DB(),
                        array(
                        ),
                        $request->getParam("count", 5)
                    );
                break;
                case "artists":
                    $metrics = \Spieldose\Metrics::GetRecentlyAddedArtists(
                        new \Spieldose\Database\DB(),
                        array(
                        ),
                        $request->getParam("count", 5)
                    );
                break;
                case "albums":
                    $metrics = \Spieldose\Metrics::GetRecentlyAddedAlbums(
                        new \Spieldose\Database\DB(),
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
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/metrics/recently_played', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/metrics/recently_played' route");
        $entity = $request->getParam("entity", "");
        if (! empty($entity)) {
            switch($entity) {
                case "tracks":
                    $metrics = \Spieldose\Metrics::GetRecentlyPlayedTracks(
                        new \Spieldose\Database\DB(),
                        array(
                        ),
                        $request->getParam("count", 5)
                    );
                break;
                case "artists":
                    $metrics = \Spieldose\Metrics::GetRecentlyPlayedArtists(
                        new \Spieldose\Database\DB(),
                        array(
                        ),
                        $request->getParam("count", 5)
                    );
                break;
                case "albums":
                    $metrics = \Spieldose\Metrics::GetRecentlyPlayedAlbums(
                        new \Spieldose\Database\DB(),
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
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/metrics/play_stats', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/metrics/play_stats' route");
        $metrics = \Spieldose\Metrics::GetPlayStats(
            new \Spieldose\Database\DB(),
            array(
            )
        );
        return $response->withJson(['metrics' => $metrics], 200);
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

?>
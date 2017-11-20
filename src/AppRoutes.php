<?php
    declare(strict_types=1);

    use Slim\Http\Request;
    use Slim\Http\Response;

    $this->app->get('/', function (Request $request, Response $response, array $args) {
        $this->logger->info($request->getOriginalMethod() . " " . $request->getUri()->getPath());
        return $this->view->render($response, 'index.html.twig', array(
            'settings' => $this->settings["twigParams"]
        ));
    });

    $this->app->group("/api", function() {

        /* user */

        $this->get('/user/poll', function (Request $request, Response $response, array $args) {
            if (\Spieldose\User::isLogged()) {
                return $response->withJson(['success' => true], 200);
            } else {
                return $response->withJson(['success' => false], 403);
            }
        });

        $this->post('/user/signin', function (Request $request, Response $response, array $args) {
            $u = new \Spieldose\User("", $request->getParam("email", ""), $request->getParam("password", ""));
            if ($u->login(new \Spieldose\Database\DB())) {
                return $response->withJson(['logged' => true], 200);
            } else {
                return $response->withJson(['logged' => false], 401);
            }
        });

        $this->post('/user/signup', function (Request $request, Response $response, array $args) {
            $dbh = new \Spieldose\Database\DB();
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
                return $response->withJson([], 409);
            } else {
                $u->id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
                $u->add($dbh);
                return $response->withJson([], 200);
            }
        });

        $this->get('/user/signout', function (Request $request, Response $response, array $args) {
            \Spieldose\User::logout();
            return $response->withJson(['logged' => false], 200);
        });

        /* user */

        $this->group("", function() {
        /* track */

        $this->get('/track/get/{id}', function (Request $request, Response $response, array $args) {
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
        });

        $this->post('/track/{id}/love', function (Request $request, Response $response, array $args) {
            $route = $request->getAttribute('route');
            $track  = new \Spieldose\Track($route->getArgument("id"));
            $db = new \Spieldose\Database\DB();
            $loved = $track->love($db);
            return $response->withJson(['loved' => $loved ? "1": "0"], 200);
        });

        $this->post('/track/{id}/unlove', function (Request $request, Response $response, array $args) {
            $route = $request->getAttribute('route');
            $track  = new \Spieldose\Track($route->getArgument("id"));
            $db = new \Spieldose\Database\DB();
            $loved = $track->unLove($db);
            return $response->withJson(['loved' => "0" ], 200);
        });

        $this->post('/track/search', function (Request $request, Response $response, array $args) {
            $filter = array();
            $data = \Spieldose\Track::search(
                new \Spieldose\Database\DB(),
                $request->getParam("actualPage", 1),
                $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                array(
                    "text" => $request->getParam("text", ""),
                    "artist" => $request->getParam("artist", ""),
                    "album" => $request->getParam("album", ""),
                    "year" => $request->getParam("year", "")
                ),
                $request->getParam("orderBy", "")
            );
            return $response->withJson(['tracks' => $data->results, 'totalResults' => $data->totalResults, 'actualPage' => $data->actualPage, 'resultsPage' => $data->resultsPage, 'totalPages' => $data->totalPages], 200);
        });

        /* track */

        /* artist */

        $this->post('/artist/search', function (Request $request, Response $response, array $args) {
            $data = \Spieldose\Artist::search(
                new \Spieldose\Database\DB(),
                $request->getParam("actualPage", 1),
                $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
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
        });

        $this->get('/artist/{name}', function (Request $request, Response $response, array $args) {
            $route = $request->getAttribute('route');
            $artist = new \Spieldose\Artist($route->getArgument("name"));
            $artist->get(new \Spieldose\Database\DB());
            return $response->withJson(['artist' => $artist], 200);
        });

        /* artist */

        /* album */

        $this->post('/album/search', function (Request $request, Response $response, array $args) {
            $data = \Spieldose\Album::search(
                new \Spieldose\Database\DB(),
                $request->getParam("actualPage", 1),
                $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
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
        });

        /* album */

        /* path */

        $this->get('/path/search', function (Request $request, Response $response, array $args) {
            return $response->withJson(
                [
                    'paths' => \Spieldose\Path::getPaths(new \Spieldose\Database\DB())
                ],
                200
            );
        });

        /* path */

        /* playlist */

        $this->get('/playlist/{id}', function (Request $request, Response $response, array $args) {
            $route = $request->getAttribute('route');
            $playlist = new \Spieldose\Playlist($route->getArgument("id"));
            $playlist->get(new \Spieldose\Database\DB());
            return $response->withJson(['playlist' => $playlist], 200);
        });

        $this->post('playlist/search', function (Request $request, Response $response, array $args) {
            $data = \Spieldose\Playlist::search(
                new \Spieldose\Database\DB(),
                $request->getParam("actualPage", 1),
                $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                array(
                    "text" => $request->getParam("text", "")
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

        /* playlist */

        /* global search */

        $this->post('/search/global', function (Request $request, Response $response, array $args) {
            $artistData = \Spieldose\Artist::search(
                new \Spieldose\Database\DB(),
                $request->getParam("actualPage", 1),
                $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                array(
                    "text" => $request->getParam("text", "")
                ),
                $request->getParam("orderBy", "")
            );
            $albumData = \Spieldose\Album::search(
                new \Spieldose\Database\DB(),
                $request->getParam("actualPage", 1),
                $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                array(
                    "text" => $request->getParam("text", "")
                ),
                $request->getParam("orderBy", "")
            );
            $trackData = \Spieldose\Track::search(
                new \Spieldose\Database\DB(),
                $request->getParam("actualPage", 1),
                $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                array(
                    "text" => $request->getParam("text", "")
                ),
                $request->getParam("orderBy", "")
            );
            $playlistData = \Spieldose\Playlist::search(
                new \Spieldose\Database\DB(),
                $request->getParam("actualPage", 1),
                $request->getParam("resultsPage", $this->get('settings')['common']['defaultResultsPage']),
                array(
                    "text" => $request->getParam("text", "")
                ),
                $request->getParam("orderBy", "")
            );
            return $response->withJson(['artists' => $artistData->results, 'albums' => $albumData->results, 'tracks' => $trackData->results, 'playlists' => $playlistData->results], 200);
        });

        /* global search */

        /* metrics */

        $this->post('/metrics/top_played_tracks', function (Request $request, Response $response, array $args) {
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
        });

        $this->post('/metrics/top_artists', function (Request $request, Response $response, array $args) {
            $metrics = \Spieldose\Metrics::GetTopArtists(
                new \Spieldose\Database\DB(),
                array(
                    "fromDate" => $request->getParam("fromDate", ""),
                    "toDate" => $request->getParam("toDate", ""),
                ),
                $request->getParam("count", 5)
            );
            return $response->withJson(['metrics' => $metrics], 200);
        });

        $this->post('/metrics/top_genres', function (Request $request, Response $response, array $args) {
            $metrics = \Spieldose\Metrics::GetTopGenres(
                new \Spieldose\Database\DB(),
                array(
                    "fromDate" => $request->getParam("fromDate", ""),
                    "toDate" => $request->getParam("toDate", ""),
                ),
                $request->getParam("count", 5)
            );
            return $response->withJson(['metrics' => $metrics], 200);
        });

        $this->post('/metrics/recently_added', function (Request $request, Response $response, array $args) {
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
        });

        $this->post('/metrics/recently_played', function (Request $request, Response $response, array $args) {
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
        });

        $this->post('/metrics/play_stats_by_hour', function (Request $request, Response $response, array $args) {
            $metrics = \Spieldose\Metrics::GetPlayStatsByHour(
                new \Spieldose\Database\DB(),
                array(
                )
            );
            return $response->withJson(['metrics' => $metrics], 200);
        });

        $this->post('/metrics/play_stats_by_weekday', function (Request $request, Response $response, array $args) {
            $metrics = \Spieldose\Metrics::GetPlayStatsByWeekDay(
                new \Spieldose\Database\DB(),
                array(
                )
            );
            return $response->withJson(['metrics' => $metrics], 200);
        });

        $this->post('/metrics/play_stats_by_month', function (Request $request, Response $response, array $args) {
            $metrics = \Spieldose\Metrics::GetPlayStatsByMonth(
                new \Spieldose\Database\DB(),
                array(
                )
            );
            return $response->withJson(['metrics' => $metrics], 200);
        });

        $this->post('/metrics/play_stats_by_year', function (Request $request, Response $response, array $args) {
            $metrics = \Spieldose\Metrics::GetPlayStatsByYear(
                new \Spieldose\Database\DB(),
                array(
                )
            );
            return $response->withJson(['metrics' => $metrics], 200);
        });

        /* metrics */
    })->add(new \Spieldose\Middleware\CheckAuth($this->getContainer()));

    })->add(new \Spieldose\Middleware\APIExceptionCatcher($this->app->getContainer()));

?>
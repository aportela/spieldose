<?php
    declare(strict_types=1);

    use Slim\Http\Request;
    use Slim\Http\Response;

    $app->get('/', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton GET '/' route");
        return $response->withRedirect('/login');
    });

    $app->get('/login', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton GET '/login' route");
        return $this->view->render($response, 'login.html.twig', []);
    });

    $app->get('/app', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton GET '/app' route");
        return $this->view->render($response, 'app.html.twig', []);
    });

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
                ->withHeader('Content-Type', "audio/mpeg")
                ->withHeader('Content-Disposition', 'attachment; filename="' . basename($track->path) . '"')
                ->withHeader('Content-Length', $filesize)
                ->withHeader('Content-Range', 'bytes ' . $offset . '-' . ($offset + $length - 1) . '/' . $filesize)
                ->withHeader('Accept-Ranges', 'bytes')
                ->write($data);
            } else {
                return $response->withStatus(200)
                    ->withHeader('Content-Type', "audio/mpeg")
                    ->withHeader('Content-Disposition', 'attachment; filename="' . basename($track->path) . '"')
                    ->withHeader('Content-Length', $filesize)
                    ->withHeader('Accept-Ranges', 'bytes')
                    ->write($data);
            }
        } else {
            throw new \Spieldose\NotFoundException("id");
        }
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/track/search', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/track/search' route");
        $data = \Spieldose\Track::search(
            new \Spieldose\Database\DB(),
            1,
            16,
            array(),
            "random"
        );
        return $response->withJson(['tracks' => $data->results, 'totalResults' => $data->totalResults, 'actualPage' => $data->actualPage, 'resultsPage' => $data->resultsPage, 'totalPages' => $data->totalPages], 200);
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/artist/search', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/artist/search' route");
        $data = \Spieldose\Artist::search(
            new \Spieldose\Database\DB(),
            $request->getParam("actualPage"),
            $request->getParam("resultsPage"),
            array(),
            ""
        );
        return $response->withJson(['artists' => $data->results, 'totalResults' => $data->totalResults, 'actualPage' => $data->actualPage, 'resultsPage' => $data->resultsPage, 'totalPages' => $data->totalPages], 200);
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

    $app->post('/api/album/search', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/album/search' route");
        $data = \Spieldose\Album::search(
            new \Spieldose\Database\DB(),
            $request->getParam("actualPage"),
            $request->getParam("resultsPage"),
            array(),
            ""
        );
        return $response->withJson(['albums' => $data->results, 'totalResults' => $data->totalResults, 'actualPage' => $data->actualPage, 'resultsPage' => $data->resultsPage, 'totalPages' => $data->totalPages], 200);
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

?>
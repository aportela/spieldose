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

    $app->post('/api/track/search', function (Request $request, Response $response, array $args) {
        $this->logger->info("Slim-Skeleton POST '/api/track/search' route");
        $data = \Spieldose\Track::search(
            new \Spieldose\Database\DB(),
            1,
            16,
            array(),
            ""
        );

        return $response->withJson(['tracks' => $data->results, 'totalResults' => $data->totalResults, 'actualPage' => $data->actualPage, 'resultsPage' => $data->resultsPage, 'totalPages' => $data->totalPages], 200);
    })->add(new \Spieldose\Middleware\APIExceptionCatcher);

?>
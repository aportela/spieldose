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
        //$v = new \Spieldose\Database\Version(new \Spieldose\Database\DB($this->get(PDO::class), $this->get(\Monolog\Logger::class)), $this->get('settings')['database']['type']);

        $settings = $this->get('settings');
        return $this->get('Twig')->render($response, 'index.html.twig', [
            //'settings' => $this->get('settings'),
            //'settings' => $settings["twigParams"],
            "locale" => $settings['common']['locale'],
            "webpack" => $settings['webpack'],
            'initialState' => json_encode(
                array(
                    "logged" => \Spieldose\User::isLogged(),
                    "sessionExpireMinutes" => session_cache_expire(),
                    //'upgradeAvailable' => $v->hasUpgradeAvailable(),
                    "defaultResultsPage" => $settings['common']['defaultResultsPage'],
                    "allowSignUp" => $settings['common']['allowSignUp'],
                    "liveSearch" => $settings['common']['liveSearch'],
                    "locale" => $settings['common']['locale']
                )
            )
        ]);
    });

    $app->group("/api2", function (Slim\Routing\RouteCollectorProxy $group) {
        $group->get('/track/search', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
            $db = $this->get(DB::class);
            $payload = array(
                "tracks" => \Spieldose\Track::searchNew($db)
            );
            $response->getBody()->write(json_encode($payload));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });
        $group->get('/track/thumbnail/{width}/{height}/{id}', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, array $args) {
            $db = $this->get(DB::class);
            $localPath = \Spieldose\Track::getLocalThumbnail($db, $args["id"], intval($args["width"]), intval($args["height"]));
            if (!empty($localPath) && file_exists(($localPath))) {
                $filesize = filesize($localPath);
                $f = fopen($localPath, 'r');
                fseek($f, 0);
                $data = fread($f, $filesize);
                fclose($f);
                $response->getBody()->write($data);
                return $response
                    ->withHeader('Content-Type', "image/jpeg")
                    ->withHeader('Content-Length', $filesize)
                    ->withStatus(200);
            } else {
                throw new \Exception("Invalid / empty path: " . $localPath);
            }
        });
    })->add(\Spieldose\Middleware\APIExceptionCatcher::class);
};

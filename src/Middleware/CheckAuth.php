<?php

    declare(strict_types=1);

    namespace Spieldose\Middleware;

    class CheckAuth {

        private $container;

        public function __construct($container) {
            $this->container = $container;
        }

        /**
         * middleware to check api methods with auth required
         *
         * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
         * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
         * @param  callable                                 $next     Next middleware
         *
         * @return \Psr\Http\Message\ResponseInterface
         */
        public function __invoke($request, $response, $next)
        {
            if (\Spieldose\User::isLogged()) {
                $response = $next($request, $response);
                return $response;
            } else {
                $this->container["apiLogger"]->warning("this api call requires authentication");
                return $response->withJson([], 401);
            }
        }
    }

?>
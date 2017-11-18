<?php

    declare(strict_types=1);

    namespace Spieldose\Middleware;

    class APIExceptionCatcher {

        private $container;

        public function __construct($container) {
            $this->container = $container;
        }

        /**
         * Example middleware invokable class
         *
         * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
         * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
         * @param  callable                                 $next     Next middleware
         *
         * @return \Psr\Http\Message\ResponseInterface
         */
        public function __invoke($request, $response, $next)
        {
            try {
                $this->container["apiLogger"]->info($request->getOriginalMethod() . " " . $request->getUri()->getPath());
                $this->container["apiLogger"]->debug($request->getBody());
                $response = $next($request, $response);
                return $response;
            } catch (\Spieldose\Exception\InvalidParamsException $e) {
                $this->container["apiLogger"]->info("Spieldose API Exception: " . $e->getMessage());
                return $response->withJson(['invalidOrMissingParams' => array($e->getMessage())], 400);
            } catch (\Spieldose\Exception\NotFoundException $e) {
                $this->container["apiLogger"]->info("Spieldose API Exception: " . $e->getMessage());
                return $response->withJson(['keyNotFound' => $e->getMessage()], 404);
            } catch (\Spieldose\Exception\AccessDenied $e) {
                $this->container["apiLogger"]->info("Spieldose API Exception: " . $e->getMessage());
                return $response->withJson([], 403);
            } catch (\Throwable $e) {
                $this->container["apiLogger"]->info("Spieldose API Exception: " . $e->getMessage());
                return $response->withJson(['exceptionDetails' => $e->getMessage()], 500);
            }
        }
    }

?>
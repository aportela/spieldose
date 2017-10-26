<?php

    declare(strict_types=1);

    namespace Spieldose\Middleware;

    class APIExceptionCatcher {
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
                $response = $next($request, $response);
                return $response;
            } catch (\Spieldose\Exception\InvalidParamsException $e) {
                return $response->withJson(['invalidOrMissingParams' => array($e->getMessage())], 400);
            } catch (\Spieldose\Exception\NotFoundException $e) {
                return $response->withJson(['keyNotFound' => $e->getMessage()], 404);
            } catch (\Spieldose\Exception\AccessDenied $e) {
                return $response->withJson([], 403);
            } catch (\Throwable $e) {
                return $response->withJson(['exceptionDetails' => $e->getMessage()], 500);
            }
        }
    }

?>
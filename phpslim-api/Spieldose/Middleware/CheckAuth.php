<?php

declare(strict_types=1);

namespace Spieldose\Middleware;

class CheckAuth
{
    /**
     * middleware to check api methods with auth required
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest PSR7 request
     * @param \Psr\Http\Server\RequestHandlerInterface $requestHandler PSR7 request handler object
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $serverRequest, \Psr\Http\Server\RequestHandlerInterface $requestHandler): \Psr\Http\Message\ResponseInterface
    {
        if (\Spieldose\UserSession::isLogged()) {
            return $requestHandler->handle($serverRequest);
        } else {
            throw new \Spieldose\Exception\UnauthorizedException($serverRequest->getMethod() . " " . $serverRequest->getUri()->getPath());
        }
    }
}

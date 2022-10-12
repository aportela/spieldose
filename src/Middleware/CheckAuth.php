<?php

declare(strict_types=1);

namespace Spieldose\Middleware;

class CheckAuth
{

    protected $logger;

    public function __construct(\Spieldose\Logger\HTTPRequestLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * middleware to check api methods with auth required
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Server\RequestHandlerInterface $handler  PSR7 request handler object
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
    {
        if (\Spieldose\User::isLogged()) {
            $response = $handler->handle($request);
            return $response;
        } else {
            throw new \Spieldose\Exception\AuthenticationMissingException($request->getMethod() . " " . $request->getUri()->getPath());
        }
    }
}

<?php

declare(strict_types=1);

namespace Spieldose\Middleware;

class JWT
{
    protected \Psr\Log\LoggerInterface $logger;

    private readonly string $passphrase;

    protected \aportela\DatabaseWrapper\DB $dbh;

    public function __construct(\Psr\Container\ContainerInterface $container)
    {
        $logger = $container->get(\Spieldose\Logger\DefaultLogger::class);
        if (! $logger instanceof \Spieldose\Logger\DefaultLogger) {
            throw new \RuntimeException("Failed to get logger (DefaultLogger) from container");
        }

        $this->logger = $logger;
        $this->passphrase = new \Spieldose\Settings()->getJWTPassphrase();
        $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
        if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
            throw new \RuntimeException("Failed to create database handler from container");
        }

        $this->dbh = $dbh;
    }

    /**
     * middleware to manage JWT authentication HTTP header
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest PSR7 request
     * @param \Psr\Http\Server\RequestHandlerInterface $requestHandler PSR7 request handler object
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $serverRequest, \Psr\Http\Server\RequestHandlerInterface $requestHandler): \Psr\Http\Message\ResponseInterface
    {
        $clientHeaderJWT = $serverRequest->hasHeader("SPIELDOSE-JWT") ? $serverRequest->getHeader("SPIELDOSE-JWT")[0] : null;
        // user not logged (or session lost) && jwt auth header found => re-auth with jwt
        if (!\Spieldose\UserSession::isLogged() && !empty($clientHeaderJWT)) {
            // try decoding jwt data
            $jwt = new \Spieldose\JWT($this->logger, $this->passphrase);
            $decoded = $jwt->decode($clientHeaderJWT);
            if (property_exists($decoded, "data") && is_object($decoded->data) && property_exists($decoded->data, "userId") && is_string($decoded->data->userId) && property_exists($decoded->data, "email") && is_string($decoded->data->email)) {
                $this->logger->notice("JWT valid data decoded", [print_r($decoded->data, true)]);
                $user = new \Spieldose\User($decoded->data->userId);
                if ($user->exists($this->dbh)) {
                    \Spieldose\UserSession::set($decoded->data->userId, $decoded->data->email);
                } else {
                    throw new \Spieldose\Exception\NotFoundException("userId");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("jwt");
            }

            $response = $requestHandler->handle($serverRequest);
            return $response->withHeader("Spieldose-JWT", $clientHeaderJWT);
        } else {
            $response = $requestHandler->handle($serverRequest);
            if (empty($clientHeaderJWT) && \Spieldose\UserSession::isLogged()) {
                $payload = [
                    "userId" => $_SESSION["userId"] ?? null,
                    "email" => $_SESSION["email"] ?? null
                ];
                $jwt = new \Spieldose\JWT($this->logger, $this->passphrase);
                $clientHeaderJWT = $jwt->encode($payload);
            }

            if ($clientHeaderJWT && \Spieldose\UserSession::isLogged()) {
                return $response->withHeader("SPIELDOSE-JWT", $clientHeaderJWT);
            } else {
                return ($response);
            }
        }
    }
}

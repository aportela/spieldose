<?php

declare(strict_types=1);

namespace Spieldose\Middleware;

class JWT
{
    protected $logger;
    private $passphrase;
    protected $dbh;

    public function __construct(\Psr\Container\ContainerInterface $container)
    {
        $this->logger = $container->get(\Spieldose\Logger\HTTPRequestLogger::class);
        $this->passphrase = $container->get('settings')['jwt']['passphrase'];
        $this->dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
    }

    /**
     * middleware to manage JWT authentication HTTP header
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Server\RequestHandlerInterface $handler  PSR7 request handler object
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
    {
        $clientHeaderJWT = $request->hasHeader("SPIELDOSE-JWT") ? $request->getHeader("SPIELDOSE-JWT")[0] : null;
        // user not logged (or session lost) && jwt auth header found => re-auth with jwt
        if (!\Spieldose\UserSession::isLogged() && !empty($clientHeaderJWT)) {
            // try decoding jwt data
            $jwt = new \Spieldose\JWT($this->logger, $this->passphrase);
            $decoded = $jwt->decode($clientHeaderJWT);
            if (isset($decoded) && isset($decoded->data) && isset($decoded->data->userId) && isset($decoded->data->email)) {
                $this->logger->notice("JWT valid data decoded", [print_r($decoded->data, true)]);
                $user = new \Spieldose\User($decoded->data->userId);
                if (!$user->exists($this->dbh)) {
                    \Spieldose\UserSession::set($decoded->data->userId, $decoded->data->email);
                } else {
                    throw new \Spieldose\Exception\NotFoundException("userId");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("jwt");
            }
            $response = $handler->handle($request);
            if (!empty($clientHeaderJWT)) {
                return $response->withHeader("SPIELDOSE-JWT", $clientHeaderJWT);
            } else {
                return ($response);
            }
        } else {
            $response = $handler->handle($request);
            if (empty($clientHeaderJWT)) {
                if (\Spieldose\UserSession::isLogged()) {
                    $payload = array(
                        "userId" => isset($_SESSION["userId"]) ? $_SESSION["userId"] : null,
                        "email" => isset($_SESSION["email"]) ? $_SESSION["email"] : null
                    );
                    $jwt = new \Spieldose\JWT($this->logger, $this->passphrase);
                    $clientHeaderJWT = $jwt->encode($payload);
                }
            }
            if ($clientHeaderJWT && \Spieldose\UserSession::isLogged()) {
                return $response->withHeader("SPIELDOSE-JWT", $clientHeaderJWT);
            } else {
                return ($response);
            }
        }
    }
}

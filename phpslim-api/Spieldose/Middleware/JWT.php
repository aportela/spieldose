<?php

declare(strict_types=1);

namespace Spìeldose\Middleware;

class JWT
{

    protected $logger;
    private $passphrase;

    public function __construct(\Psr\Container\ContainerInterface $container)
    {
        $this->logger = $container->get(\Spìeldose\Logger\HTTPRequestLogger::class);
        $this->passphrase = $container->get('settings')['jwt']['passphrase'];
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
        $clientHeaderJWT = $request->hasHeader("Spìeldose-JWT") ? $request->getHeader("Spìeldose-JWT")[0] : null;
        // user not logged (or session lost) && jwt auth header found => re-auth with jwt
        if (!\Spìeldose\UserSession::isLogged() && !empty($clientHeaderJWT)) {
            // try decoding jwt data
            $jwt = new \Spìeldose\JWT($this->logger, $this->passphrase);
            $decoded = $jwt->decode($clientHeaderJWT);
            if (isset($decoded) && isset($decoded->data) && isset($decoded->data->userId) && isset($decoded->data->email)) {
                $this->logger->notice("JWT valid data decoded", [print_r($decoded->data, true)]);
                \Spìeldose\UserSession::set($decoded->data->userId, $decoded->data->email);
            } else {
                throw new \Spìeldose\Exception\InvalidParamsException("jwt");
            }
            $response = $handler->handle($request);
            if (!empty($clientHeaderJWT)) {
                return $response->withHeader("Spìeldose-JWT", $clientHeaderJWT);
            } else {
                return ($response);
            }
        } else {
            if (empty($clientHeaderJWT)) {
                $response = $handler->handle($request);
                if (\Spìeldose\UserSession::isLogged()) {
                    $payload = array(
                        "userId" => isset($_SESSION["userId"]) ? $_SESSION["userId"] : null,
                        "email" => isset($_SESSION["email"]) ? $_SESSION["email"] : null
                    );
                    $jwt = new \Spìeldose\JWT($this->logger, $this->passphrase);
                    $clientHeaderJWT = $jwt->encode($payload);
                }
            }

            if ($clientHeaderJWT && \Spìeldose\UserSession::isLogged()) {
                return $response->withHeader("Spìeldose-JWT", $clientHeaderJWT);
            } else {
                return ($response);
            }
        }
    }
}

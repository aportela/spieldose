<?php

declare(strict_types=1);

namespace Spieldose\Middleware;

class JWT
{

    protected $logger;
    private $passphrase;

    public function __construct(\Psr\Container\ContainerInterface $container)
    {
        $this->logger = $container->get(\Spieldose\Logger\HTTPRequestLogger::class);
        $this->passphrase = $container->get('settings')['jwt']['passphrase'];
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
        $clientHeaderJWT = $request->hasHeader("SPIELDOSE-JWT") ? $request->getHeader("SPIELDOSE-JWT")[0] : null;
        if (!\Spieldose\User::isLogged()) {
            $this->logger->debug("User not logged");
            if (!empty($clientHeaderJWT)) {
                $this->logger->debug("JWT found in client headers", [$clientHeaderJWT]);
                try {
                    $jwt = new \Spieldose\JWT($this->logger, $this->passphrase);
                    $decoded = $jwt->decode($clientHeaderJWT);
                    if (isset($decoded) && isset($decoded->data) && isset($decoded->data->userId) && isset($decoded->data->email)) {
                        $this->logger->debug("JWT decoded", $decoded->data);
                        $_SESSION["userId"] = $decoded->data->userId;
                        $_SESSION["email"] = $decoded->data->email;
                    } else {
                        $this->logger->warning("Error decoding JWT");
                        throw new \Spieldose\Exception\InvalidParamsException("JWT: " . $clientHeaderJWT);
                    }
                } catch (\Throwable $e) {
                    $this->logger->error("Error decoding JWT", [$e->getMessage()]);
                }
            }
            $response = $handler->handle($request);
            return $response->withHeader("SPIELDOSE-JWT", $clientHeaderJWT);
        } else {
            $this->logger->debug("User logged");
            if (empty($clientHeaderJWT)) {
                $this->logger->debug("JWT not found in client headers");
                $payload = array(
                    "id" => isset($_SESSION["userId"]) ? $_SESSION["userId"] : null,
                    "email" => isset($_SESSION["email"]) ? $_SESSION["email"] : null
                );
                try {
                    $jwt = new \Spieldose\JWT($this->logger, $this->passphrase);
                    $clientHeaderJWT = $jwt->encode($payload);
                    $this->logger->debug("New JWT", [$clientHeaderJWT]);
                } catch (\Throwable $e) {
                    $this->logger->error("Error encoding JWT payload", [$payload]);
                }
            }
            $response = $handler->handle($request);
            if ($clientHeaderJWT) {
                return $response->withHeader("SPIELDOSE-JWT", $clientHeaderJWT);
            } else {
                return ($response);
            }
        }
    }
}

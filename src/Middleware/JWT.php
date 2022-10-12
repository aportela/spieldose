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
            if (!empty($clientHeaderJWT)) {
                try {
                    $jwt = new \Spieldose\JWT($this->logger, $this->passphrase);
                    $decoded = $jwt->decode($clientHeaderJWT);

                    if (isset($decoded) && isset($decoded->data) && isset($decoded->data->userId) && isset($decoded->data->email)) {
                        $this->logger->debug("JWT decoded", $decoded->data);
                        $_SESSION["userId"] = $decoded->data->userId;
                        $_SESSION["email"] = $decoded->data->email;
                    } else {
                        throw new \Spieldose\Exception\InvalidParamsException("jwt");
                    }
                } catch (\InvalidArgumentException $e) {
                    // provided key/key-array is empty or malformed.
                    $this->logger->error("Error decoding JWT", [$e->getMessage()]);
                } catch (\DomainException $e) {
                    // provided algorithm is unsupported OR
                    // provided key is invalid OR
                    // unknown error thrown in openSSL or libsodium OR
                    // libsodium is required but not available.
                    $this->logger->error("Error decoding JWT", [$e->getMessage()]);
                } catch (\Firebase\JWT\SignatureInvalidException $e) {
                    // provided JWT signature verification failed.
                    $this->logger->error("Error decoding JWT", [$e->getMessage()]);
                } catch (\Firebase\JWT\BeforeValidException $e) {
                    // provided JWT is trying to be used before "nbf" claim OR
                    // provided JWT is trying to be used before "iat" claim.
                    $this->logger->error("Error decoding JWT", [$e->getMessage()]);
                } catch (\Firebase\JWT\ExpiredException $e) {
                    // provided JWT is trying to be used after "exp" claim.
                    $this->logger->error("Error decoding JWT", [$e->getMessage()]);
                } catch (\UnexpectedValueException $e) {
                    // provided JWT is malformed OR
                    // provided JWT is missing an algorithm / using an unsupported algorithm OR
                    // provided JWT algorithm does not match provided key OR
                    // provided key ID in key/key-array is empty or invalid.
                    $this->logger->error("Error decoding JWT", [$e->getMessage()]);
                }
            }
            $response = $handler->handle($request);
            if (!empty($clientHeaderJWT)) {
                return $response->withHeader("SPIELDOSE-JWT", "BB"); //$clientHeaderJWT);
            } else {
                return ($response);
            }
        } else {
            $this->logger->debug("User logged");
            if (empty($clientHeaderJWT)) {
                $payload = array(
                    "id" => isset($_SESSION["userId"]) ? $_SESSION["userId"] : null,
                    "email" => isset($_SESSION["email"]) ? $_SESSION["email"] : null
                );
                try {
                    $jwt = new \Spieldose\JWT($this->logger, $this->passphrase);
                    $clientHeaderJWT = $jwt->encode($payload);
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

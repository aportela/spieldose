<?php

namespace Spìeldose;

class JWT
{
    const ALGORITHM = 'HS256';

    private $logger;
    private $passphrase;

    /**
     * jwt constructor
     *
     * @param string $passphrase
     */
    public function __construct(\Psr\Log\LoggerInterface $logger, string $passphrase)
    {
        $this->logger = $logger;
        $this->passphrase = $passphrase;
        $this->logger->debug("JWT passphrase", [$passphrase]);
    }

    public function __destruct()
    {
    }

    public function encode($payload): string
    {
        $jwt = null;
        $this->logger->notice("JWT encoding", [$payload]);
        try {
            $jwt = \Firebase\JWT\JWT::encode(
                array(
                    'iat' => time(),
                    'data' => $payload
                ),
                $this->passphrase,
                self::ALGORITHM
            );
        } catch (\Throwable $e) {
            $this->logger->error("JWT encoding error", [$e->getMessage()]);
        } finally {
            return ($jwt);
        }
    }

    public function decode(string $jwt): \stdClass
    {
        $data = null;
        try {
            $this->logger->notice("JWT decoding", [$jwt]);
            $data = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($this->passphrase, self::ALGORITHM));
        } catch (\InvalidArgumentException $e) {
            // provided key/key-array is empty or malformed.
            $this->logger->error("JWT decoding error", [$e->getMessage()]);
        } catch (\DomainException $e) {
            // provided algorithm is unsupported OR
            // provided key is invalid OR
            // unknown error thrown in openSSL or libsodium OR
            // libsodium is required but not available.
            $this->logger->error("JWT decoding error", [$e->getMessage()]);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            // provided JWT signature verification failed.
            $this->logger->error("JWT decoding error", [$e->getMessage()]);
        } catch (\Firebase\JWT\BeforeValidException $e) {
            // provided JWT is trying to be used before "nbf" claim OR
            // provided JWT is trying to be used before "iat" claim.
            $this->logger->error("JWT decoding error", [$e->getMessage()]);
        } catch (\Firebase\JWT\ExpiredException $e) {
            // provided JWT is trying to be used after "exp" claim.
            $this->logger->error("JWT decoding error", [$e->getMessage()]);
        } catch (\UnexpectedValueException $e) {
            // provided JWT is malformed OR
            // provided JWT is missing an algorithm / using an unsupported algorithm OR
            // provided JWT algorithm does not match provided key OR
            // provided key ID in key/key-array is empty or invalid.
            $this->logger->error("JWT decoding error", [$e->getMessage()]);
        } catch (\Throwable $e) {
            $this->logger->error("JWT decoding error", [$e->getMessage()]);
        } finally {
            return ($data);
        }
    }
}

<?php

namespace Spieldose;

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
        }
        return ($jwt);
    }

    public function decode(string $jwt)
    {
        $data = null;
        try {
            $this->logger->notice("JWT decoding", [$jwt]);
            $data = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($this->passphrase, self::ALGORITHM));
        } catch (\Throwable $e) {
            $this->logger->error("JWT decoding error", [$e->getMessage()]);
        }
        return ($data);
    }
}

<?php

namespace Spieldose;

class JWT
{
    private $logger;
    private $passphrase;

    /**
     * user constructor
     *
     * @param string $passphrase
     */
    public function __construct(\Psr\Log\LoggerInterface $logger, string $passphrase)
    {
        $this->logger = $logger;
        $this->passphrase = $passphrase;
        $this->logger->debug("JWT passphrase: " . $passphrase);
    }

    public function __destruct()
    {
    }

    public function encode($payload): string
    {
        $this->logger->log("JWT encoding", $payload);
        return (\Firebase\JWT\JWT::encode($payload, $this->passphrase, 'HS256'));
    }

    public function decode(string $jwt)
    {
        $data = null;
        try {
            $this->logger->log("JWT decoding", $jwt);
            $data = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($this->passphrase, 'HS256'));
        } catch (\Throwable $e) {
            $this->logger->error("JWT decoding error", [$e->getMessage()]);
        }
        return ($data);
    }
}

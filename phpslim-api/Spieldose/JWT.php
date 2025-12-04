<?php

declare(strict_types=1);

namespace Spieldose;

class JWT
{
    public const ALGORITHM = 'HS256';

    public function __construct(private readonly \Psr\Log\LoggerInterface $logger, private readonly string $passphrase)
    {
        $this->logger->debug("JWT passphrase", [$this->passphrase]);
    }

    public function encode(string $subject, int $expiresAt): string
    {
        $currentTimestamp = time();
        if ($expiresAt !== 0 && $expiresAt <= $currentTimestamp) {
            throw new \InvalidArgumentException("expiresAt");
        } else {
            try {
                $jwtPayload = [
                    'iat' => $currentTimestamp,
                    'sub' => $subject,
                ];
                if ($expiresAt > 0) {
                    $jwtPayload['exp'] = $expiresAt;
                }

                return (\Firebase\JWT\JWT::encode(
                    $jwtPayload,
                    $this->passphrase,
                    self::ALGORITHM
                ));
            } catch (\Throwable $throwable) {
                $this->logger->error("JWT encoding error", [$throwable->getMessage()]);
                throw $throwable;
            }
        }
    }

    public function decode(string $jwt): \stdClass
    {
        $data = new \stdClass();
        $this->logger->notice("JWT decoding", [$jwt]);
        $data = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($this->passphrase, self::ALGORITHM));
        $this->logger->debug("Decoded JWT ", [$data]);
        return ($data);
    }
}

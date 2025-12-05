<?php

declare(strict_types=1);

namespace Spieldose\Middleware;

class APIExceptionCatcher
{
    private readonly bool $debug;

    public function __construct(protected \Psr\Log\LoggerInterface $logger)
    {
        $settings = new \Spieldose\Settings();
        $this->debug = $settings->getEnvironment() === 'development';
    }

    /**
     * @return array<mixed>
     */
    private function getExceptionData(\Throwable $throwable): array
    {
        return ([
            'type' => $throwable::class,
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTrace(),
        ]);
    }

    /**
     * @param array<mixed> $payload
     */
    private function handleException(\Exception $exception, int $statusCode, array $payload): \Psr\Http\Message\ResponseInterface
    {
        $payload['APIError'] = true;
        $payload['status'] = $statusCode;
        $this->logger->error(sprintf("Exception (%d) caught (%s) - Message: %s", $statusCode, $exception::class, $exception->getMessage()), [$exception]);
        if ($this->debug) {
            $payload['exception'] = $this->getExceptionData($exception);
            $this->logger->debug("Exception: ", $payload['exception']);
        }

        $response = new \Slim\Psr7\Response();
        $json = json_encode($payload);
        if (is_string($json)) {
            $response->getBody()->write($json);
        } else {
            $this->logger->error("Error serializing payload", [$payload, json_last_error(), json_last_error_msg()]);
            $response->getBody()->write("{}");
        }

        return $response->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
    }

    private function handleGenericException(\Throwable $throwable): \Psr\Http\Message\ResponseInterface
    {
        $payload = [
            'APIError' => false,
            'status' => 500,
        ];
        $this->logger->error(sprintf("Unhandled exception (500) caught (%s) - Message: %s", $throwable::class, $throwable->getMessage()), [$throwable]);
        if ($this->debug) {
            $payload['exception'] = $this->getExceptionData($throwable);
            $this->logger->debug("Exception: ", $payload['exception']);
        }

        $this->logger->debug($throwable->getTraceAsString());
        $response = new \Slim\Psr7\Response();
        $json = json_encode($payload);
        if (is_string($json)) {
            $response->getBody()->write($json);
        } else {
            $this->logger->error("Error serializing payload", [$throwable, $payload, json_last_error(), json_last_error_msg()]);
            $response->getBody()->write("{}");
        }

        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }

    /**
     * APIExceptionCatcher middleware invokable class
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest PSR7 request
     * @param \Psr\Http\Server\RequestHandlerInterface $requestHandler PSR7 request handler object
     */
    public function __invoke(\Psr\Http\Message\ServerRequestInterface $serverRequest, \Psr\Http\Server\RequestHandlerInterface $requestHandler): \Psr\Http\Message\ResponseInterface
    {
        try {
            $this->logger->debug($serverRequest->getMethod() . " " . $serverRequest->getUri()->getPath());
            $contentType = $serverRequest->getHeaderLine('Content-Type');
            if (str_contains($contentType, 'application/json')) {
                $data = json_decode($serverRequest->getBody()->getContents(), true);
                $this->logger->debug("JSON", [$data]);
            }

            return $requestHandler->handle($serverRequest);
        } catch (\Spieldose\Exception\InvalidParamsException $e) {
            return $this->handleException($e, 400, ['invalidOrMissingParams' => explode(",", $e->getMessage())]);
        } catch (\Spieldose\Exception\AlreadyExistsException $e) {
            return $this->handleException($e, 409, ['invalidOrMissingParams' => explode(",", $e->getMessage())]);
        } catch (\Spieldose\Exception\UnauthorizedException $e) {
            return $this->handleException($e, 401, []);
        } catch (\Spieldose\Exception\AccessDeniedException $e) {
            return $this->handleException($e, 403, []);
        } catch (\Spieldose\Exception\NotFoundException $e) {
            return $this->handleException($e, 404, ['keyNotFound' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return $this->handleGenericException($e);
        }
    }
}

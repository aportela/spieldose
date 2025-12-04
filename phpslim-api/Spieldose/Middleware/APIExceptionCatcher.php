<?php

declare(strict_types=1);

namespace Spieldose\Middleware;

class APIExceptionCatcher
{
    public function __construct(protected \Psr\Log\LoggerInterface $logger) {}

    /**
     * @param array<mixed> $payload
     */
    private function handleException(\Exception $exception, int $statusCode, array $payload): \Psr\Http\Message\ResponseInterface
    {
        $this->logger->error(sprintf("Exception caught (%s) - Message: %s", $exception::class, $exception->getMessage()));
        $response = new \Slim\Psr7\Response();
        $json = json_encode($payload);
        if (is_string($json)) {
            $response->getBody()->write($json);
        } else {
            $this->logger->error("Error serializing payload", $payload);
            $response->getBody()->write("{}");
        }

        return $response->withStatus($statusCode);
    }

    private function handleGenericException(\Throwable $throwable): \Psr\Http\Message\ResponseInterface
    {
        $this->logger->error(sprintf("Unhandled exception caught (%s) - Message: %s", $throwable::class, $throwable->getMessage()));
        $this->logger->debug($throwable->getTraceAsString());

        $response = new \Slim\Psr7\Response();
        $exception = [
            'type' => $throwable::class,
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
        ];
        $parent = $throwable->getPrevious();
        if ($parent instanceof \Throwable) {
            $exception['parent'] = [
                'type' => $parent::class,
                'message' => $parent->getMessage(),
                'file' => $parent->getFile(),
                'line' => $parent->getLine(),
            ];
        }

        $payload = json_encode([
            'exception' => $exception,
            'status' => 500,
        ]);
        if (is_string($payload)) {
            $response->getBody()->write($payload);
        } else {
            $this->logger->error("Error serializing payload", [$exception]);
            $response->getBody()->write("{}");
        }

        return $response->withStatus(500);
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
        } catch (\Spieldose\Exception\DeletedException $e) {
            return $this->handleException($e, 410, []);
        } catch (\Throwable $e) {
            return $this->handleGenericException($e);
        }
    }
}

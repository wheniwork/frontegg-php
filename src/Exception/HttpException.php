<?php

namespace Frontegg\Exception;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class HttpException extends FronteggException
{
    private ?ResponseInterface $response;
    private array $responseData;

    public function __construct(string $message, int $code = 0, ?ResponseInterface $response = null, array $responseData = [])
    {
        parent::__construct($message, $code);
        $this->response = $response;
        $this->responseData = $responseData;
    }

    public static function fromGuzzleException(GuzzleException $e): self
    {
        $response = method_exists($e, 'getResponse') ? $e->getResponse() : null;
        $responseData = [];
        
        if ($response) {
            try {
                $responseData = json_decode($response->getBody()->getContents(), true) ?? [];
            } catch (\Throwable $e) {
                // Ignore JSON decode errors
            }
        }

        return new self(
            $e->getMessage(),
            $e->getCode(),
            $response,
            $responseData
        );
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function getResponseData(): array
    {
        return $this->responseData;
    }
}

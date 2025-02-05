<?php

namespace Frontegg\Http;

use Frontegg\Config\Config;
use Frontegg\Exception\HttpException;
use Frontegg\Exception\Identity\NoTokenException;
use Frontegg\Identity\IdentityManager;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

class FronteggHttpClient
{
    private Config $config;
    private ?IdentityManager $identityManager = null;
    private GuzzleClient $client;
    private GuzzleClient $fronteggClient;

    public function __construct(Config $config)
    {
        $this->config = $config;

        // Client for configured base URL (e.g. auth.whenidev.net)
        $this->client = new GuzzleClient([
            'base_uri' => $config->getBaseUrl(),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'http_errors' => false,
        ]);

        // Client for Frontegg API (e.g. api.us.frontegg.com)
        $this->fronteggClient = new GuzzleClient([
            'base_uri' => $config->getFronteggBaseUrl(),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'http_errors' => false,
        ]);
    }

    private function getClient($useCustomDomain = false)
    {
        return $useCustomDomain ? $this->fronteggClient : $this->client;
    }

    public function setIdentityManager(IdentityManager $identityManager): void
    {
        $this->identityManager = $identityManager;
    }

    /**
     * @throws HttpException
     */
    public function request(string $method, string $uri, array $options = [], bool $requiresAuth = true, bool $useCustomDomain = false): array
    {
        try {
            if ($requiresAuth && $this->identityManager !== null) {
                try {
                    $token = $this->identityManager->getToken();
                    $options['headers']['Authorization'] = "Bearer {$token}";
                } catch (NoTokenException $e) {
                    // Allow the request to proceed without a token
                }
            }

            $client = $this->getClient($useCustomDomain);
            $response = $client->request($method, $uri, $options);
            $responseData = json_decode($response->getBody()->getContents(), true) ?? [];

            if ($response->getStatusCode() >= 400) {
                throw new HttpException(
                    $responseData['errors'][0]['message'] ?? $response->getReasonPhrase(),
                    $response->getStatusCode(),
                    $response,
                    $responseData
                );
            }

            return $responseData;
        } catch (GuzzleException $e) {
            throw HttpException::fromGuzzleException($e);
        }
    }

    /**
     * @throws HttpException
     */
    public function get(string $uri, array $options = [], bool $requiresAuth = true, bool $useCustomDomain = false): array
    {
        return $this->request('GET', $uri, $options, $requiresAuth, $useCustomDomain);
    }

    /**
     * @throws HttpException
     */
    public function post(string $uri, array $options = [], bool $requiresAuth = true, bool $useCustomDomain = false): array
    {
        return $this->request('POST', $uri, $options, $requiresAuth, $useCustomDomain);
    }

    /**
     * @throws HttpException
     */
    public function put(string $uri, array $options = [], bool $useCustomDomain = false): array
    {
        return $this->request('PUT', $uri, $options, true, $useCustomDomain);
    }

    /**
     * @throws HttpException
     */
    public function delete(string $uri, array $options = [], bool $useCustomDomain = false): array
    {
        return $this->request('DELETE', $uri, $options, true, $useCustomDomain);
    }
}

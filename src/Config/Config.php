<?php

namespace Frontegg\Config;

class Config
{
    private string $region;
    private string $baseUrl;
    private string $fronteggBaseUrl;
    private string $customDomain;
    private string $clientId;
    private string $apiKey;
    private array $httpOptions;

    public function __construct(array $options = [])
    {
        $this->region = $options['region'] ?? 'us';
        $this->baseUrl = rtrim($options['baseUrl'] ?? '', '/');
        $this->fronteggBaseUrl = rtrim($options['fronteggBaseUrl'] ?? 'https://api.us.frontegg.com', '/');
        $this->customDomain = $options['customDomain'] ?? '';
        $this->clientId = $options['clientId'] ?? getenv('FRONTEGG_CLIENT_ID');
        $this->apiKey = $options['apiKey'] ?? getenv('FRONTEGG_API_KEY');
        $this->httpOptions = $options['httpOptions'] ?? [];
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getCustomDomain(): string
    {
        return $this->customDomain ?? $this->getBaseUrl();
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getFronteggBaseUrl(): string
    {
        return $this->fronteggBaseUrl;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getHttpOptions(): array
    {
        return $this->httpOptions;
    }
}

<?php

namespace Frontegg\Entities\Entitlements\SelfService;

use Frontegg\Clients\SelfService\BaseSelfServiceClient;
use Frontegg\Exception\HttpException;
use Frontegg\Entities\Entitlements\Entitlement;

class EntitlementsClient extends BaseSelfServiceClient
{
    /**
     * Create an Entitlement instance
     */
    private function createEntitlement(array $data): Entitlement 
    {
        return new Entitlement($data);
    }

    /**
     * Get current user's entitlements
     *
     * @param array $options Query parameters (offset, limit, sortType, planId, orderBy)
     * @return Entitlement[]
     * @throws HttpException
     */
    public function getMyEntitlements(array $options = []): array
    {
        $response = $this->httpClient->get('/resources/entitlements/v2', [
            'headers' => $this->getHeaders(),
            'query' => array_merge($options, ['assignLevel' => 'USER'])
        ]);
        return array_map(fn(array $data) => $this->createEntitlement($data), $response['items'] ?? []);
    }

    /**
     * Get current tenant's entitlements
     *
     * @param array $options Query parameters (offset, limit, sortType, planId, orderBy)
     * @return Entitlement[]
     * @throws HttpException
     */
    public function getTenantEntitlements(array $options = []): array
    {
        $response = $this->httpClient->get('/resources/entitlements/v2', [
            'headers' => $this->getHeaders(),
            'query' => array_merge($options, ['assignLevel' => 'TENANT'])
        ]);
        return array_map(fn(array $data) => $this->createEntitlement($data), $response['items'] ?? []);
    }

    /**
     * Check if the current user or their tenant is entitled to a specific feature
     *
     * @param string $featureKey The feature key to check
     * @return bool
     * @throws HttpException
     */
    public function isEntitledToFeature(string $featureKey): bool
    {
        try {
            $response = $this->httpClient->post('/v1/data/e10s/features/is_entitled_to_input_feature', [
                'headers' => $this->getHeaders(),
                'json' => ['featureKey' => $featureKey]
            ]);
            return $response['result'] ?? false;
        } catch (HttpException $e) {
            if ($e->getCode() === 404) {
                return false;
            }
            throw $e;
        }
    }
}

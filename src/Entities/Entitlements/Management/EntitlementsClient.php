<?php

namespace Frontegg\Entities\Entitlements\Management;

use Frontegg\Clients\Management\BaseManagementClient;
use Frontegg\Exception\HttpException;
use Frontegg\Entities\Entitlements\Entitlement;

class EntitlementsClient extends BaseManagementClient
{
    /**
     * Create an Entitlement instance
     */
    private function createEntitlement(array $data): Entitlement 
    {
        return new Entitlement($data);
    }

    /**
     * Get a list of entitlements
     *
     * @param array $options Query parameters (offset, limit, sortType, planId, planIds, assignLevel, orderBy, userIds, tenantIds, withRelations)
     * @return Entitlement[]
     * @throws HttpException
     */
    public function getEntitlements(array $options = []): array
    {
        $response = $this->httpClient->get('/resources/entitlements/v2', [
            'headers' => $this->getHeaders(),
            'query' => $options
        ]);
        return array_map(fn(array $data) => $this->createEntitlement($data), $response['items'] ?? []);
    }

    /**
     * Get a single entitlement by ID
     *
     * @param string $id
     * @return Entitlement
     * @throws HttpException
     */
    public function getEntitlement(string $id): Entitlement
    {
        $data = $this->httpClient->get("/resources/entitlements/v2/{$id}", [
            'headers' => $this->getHeaders()
        ]);
        return $this->createEntitlement($data);
    }

    /**
     * Create a new entitlement
     *
     * @param array $data Entitlement data
     * @return Entitlement
     * @throws HttpException
     */
    public function createEntitlement(array $data): Entitlement
    {
        $response = $this->httpClient->post('/resources/entitlements/v2', [
            'headers' => $this->getHeaders(),
            'json' => $data,
        ]);
        return $this->createEntitlement($response);
    }

    /**
     * Create multiple entitlements in a batch
     *
     * @param array $entitlements Array of entitlement data
     * @return Entitlement[]
     * @throws HttpException
     */
    public function createBatchEntitlements(array $entitlements): array
    {
        $response = $this->httpClient->post('/resources/entitlements/v2/batch', [
            'headers' => $this->getHeaders(),
            'json' => ['entitlements' => $entitlements],
        ]);
        return array_map(fn(array $data) => $this->createEntitlement($data), $response);
    }
}

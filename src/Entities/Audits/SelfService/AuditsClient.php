<?php

namespace Frontegg\Entities\Audits\SelfService;

use Frontegg\Clients\SelfService\BaseSelfServiceClient;
use Frontegg\Exception\HttpException;
use Frontegg\Entities\Audits\Audit;

class AuditsClient extends BaseSelfServiceClient
{
    /**
     * Create an Audit instance
     */
    private function createAudit(array $data): Audit 
    {
        return new Audit($data);
    }

    /**
     * Get audits for the current tenant
     *
     * @param array $options Query parameters (type, category, severity, from, to, offset, limit)
     * @return Audit[]
     * @throws HttpException
     */
    public function getTenantAudits(array $options = []): array
    {
        $response = $this->httpClient->get('/resources/audits/v1', [
            'headers' => $this->getHeaders(),
            'query' => $options
        ]);
        return array_map(fn(array $data) => $this->createAudit($data), $response['audits'] ?? []);
    }

    /**
     * Get audits for the current user
     *
     * @param array $options Query parameters (type, category, severity, from, to, offset, limit)
     * @return Audit[]
     * @throws HttpException
     */
    public function getMyAudits(array $options = []): array
    {
        $response = $this->httpClient->get('/resources/audits/v1/me', [
            'headers' => $this->getHeaders(),
            'query' => $options
        ]);
        return array_map(fn(array $data) => $this->createAudit($data), $response['audits'] ?? []);
    }

    /**
     * Create a new audit for the current tenant
     *
     * @param array $data Audit data
     * @return Audit
     * @throws HttpException
     */
    public function createAudit(array $data): Audit
    {
        $response = $this->httpClient->post('/resources/audits/v1', [
            'headers' => $this->getHeaders(),
            'json' => $data,
        ]);
        return $this->createAudit($response);
    }
}

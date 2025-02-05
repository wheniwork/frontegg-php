<?php

namespace Frontegg\Entities\Audits\Management;

use Frontegg\Clients\Management\BaseManagementClient;
use Frontegg\Exception\HttpException;
use Frontegg\Entities\Audits\Audit;

class AuditsClient extends BaseManagementClient
{
    /**
     * Create an Audit instance
     */
    private function createAudit(array $data): Audit 
    {
        return new Audit($data);
    }

    /**
     * Get a list of audits
     *
     * @param array $options Query parameters (tenantId, userId, type, category, severity, from, to, offset, limit)
     * @return Audit[]
     * @throws HttpException
     */
    public function getAudits(array $options = []): array
    {
        $response = $this->httpClient->get('/resources/audits/v1', [
            'headers' => $this->getHeaders(),
            'query' => $options
        ]);
        return array_map(fn(array $data) => $this->createAudit($data), $response['audits'] ?? []);
    }

    /**
     * Get a single audit by ID
     *
     * @param string $auditId
     * @return Audit
     * @throws HttpException
     */
    public function getAudit(string $auditId): Audit
    {
        $data = $this->httpClient->get("/resources/audits/v1/{$auditId}", [
            'headers' => $this->getHeaders()
        ]);
        return $this->createAudit($data);
    }

    /**
     * Create a new audit
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

    /**
     * Create multiple audits in a batch
     *
     * @param array $audits Array of audit data
     * @return Audit[]
     * @throws HttpException
     */
    public function createBatchAudits(array $audits): array
    {
        $response = $this->httpClient->post('/resources/audits/v1/batch', [
            'headers' => $this->getHeaders(),
            'json' => ['audits' => $audits],
        ]);
        return array_map(fn(array $data) => $this->createAudit($data), $response);
    }

    /**
     * Export audits to CSV
     *
     * @param array $options Query parameters (tenantId, userId, type, category, severity, from, to)
     * @return string CSV content
     * @throws HttpException
     */
    public function exportAudits(array $options = []): string
    {
        return $this->httpClient->get('/resources/audits/v1/export', [
            'headers' => $this->getHeaders(),
            'query' => $options
        ]);
    }
}

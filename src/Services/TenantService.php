<?php

namespace Versa\VersaAtende\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Versa\VersaAtende\Contracts\VersaAtendeConfigurable;
use Versa\VersaAtende\Exceptions\VersaAtendeException;

class TenantService
{
    public function __construct(
        protected string $baseUrl,
        protected string $adminKey
    ) {}

    public function create(string $name, string $slug): array
    {
        $response = Http::withHeaders([
            'x-admin-key'  => $this->adminKey,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ])->post("{$this->baseUrl}/admin/tenants", [
            'name' => $name,
            'slug' => $slug,
        ]);

        if ($response->failed()) {
            throw new VersaAtendeException(
                'Falha na comunicação com o Versa Atende: ' . ($response->json('message') ?? 'Erro desconhecido.'),
                $response->status()
            );
        }

        return $response->json();
    }

    public function activateIntegration(Model&VersaAtendeConfigurable $model, string $name, string $slug): array
    {
        $response = $this->create($name, $slug);
        $tenantData = $response['tenant'] ?? null;

        if (empty($tenantData['id']) || empty($tenantData['apiToken'])) {
            throw new VersaAtendeException('A resposta da API não retornou as credenciais obrigatórias (id ou apiToken).');
        }

        $model->versaAtendeConfig()->updateOrCreate(
            [],
            [
                'tenant_id'    => $tenantData['id'],
                'tenant_slug'  => $slug,
                'tenant_token' => $tenantData['apiToken'],
                'is_active'    => true
            ]
        );

        return $response;
    }
}
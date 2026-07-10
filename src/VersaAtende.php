<?php

namespace Versa\VersaAtende;

use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Versa\VersaAtende\Exceptions\VersaAtendeException;

class VersaAtende
{
    protected string $baseUrl;
    protected string $adminKey;

    public function __construct(string $baseUrl, string $adminKey)
    {
        $this->baseUrl = $baseUrl;
        $this->adminKey = $adminKey;
    }

    public function criarTenant(string $name, string $slug): array
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

    public function ativarParaModel(Model $model, string $name, string $slug): array
    {
        $response = $this->criarTenant($name, $slug);
        
        $dadosTenant = $response['tenant'] ?? [];
        $model->versaAtendeConfig()->updateOrCreate(
            [],
            [
                'tenant_id'    => $dadosTenant['id'] ?? null,
                'tenant_slug'  => $slug,
                'tenant_token' => $dadosTenant['apiToken'] ?? null,
                'ativado'      => true
            ]
        );

        return $response;
    }
}
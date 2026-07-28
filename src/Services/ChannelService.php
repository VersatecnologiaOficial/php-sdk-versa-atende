<?php

namespace Versa\VersaAtende\Services;

use Illuminate\Support\Facades\Http;
use Versa\VersaAtende\Exceptions\VersaAtendeException;

class ChannelService
{
    public function __construct(
        protected string $baseUrl
    ) {}

    public function get(string $tenantToken): array
    {
        $response = Http::withHeaders([
            'x-tenant-token' => $tenantToken,
            'Content-Type'   => 'application/json',
            'Accept'         => 'application/json',
        ])->get("{$this->baseUrl}/tenant/channels");

        if ($response->failed()) {
            throw new VersaAtendeException('Falha ao buscar canais: ' . $response->json('message', 'Erro desconhecido.'));
        }

        $body = $response->json();

        // Resposta esperada: { "success": true, "channels": [ { slug, name, apiKey }, ... ] }
        if (!isset($body['channels'])) {
            throw new VersaAtendeException(
                'A API retornou uma resposta inesperada ao listar canais. Body: ' . $response->body()
            );
        }

        return $body['channels'];
    }

    public function create(string $tenantToken, string $name, string $slug): array
    {
        $response = Http::withHeaders([
            'x-tenant-token' => $tenantToken,
            'Content-Type'   => 'application/json',
            'Accept'         => 'application/json',
        ])->post("{$this->baseUrl}/tenant/channels", [
            'name' => $name,
            'slug' => $slug,
        ]);

        if ($response->failed()) {
            throw new VersaAtendeException('Falha ao criar canal: ' . $response->json('message', 'Erro desconhecido.'));
        }

        $body = $response->json();

        // Resposta esperada: { "success": true, "channel": { slug, name, apiKey } }
        if (empty($body['success']) || empty($body['channel'])) {
            throw new VersaAtendeException(
                'A API retornou uma resposta inesperada ao criar o canal. Body: ' . $response->body()
            );
        }

        return $body['channel'];
    }

    /**
     * Atualiza um canal existente pelo seu slug.
     * Endpoint: PATCH /tenant/channels/{slug}
     */
    public function update(string $tenantToken, string $slug, string $name): array
    {
        $response = Http::withHeaders([
            'x-tenant-token' => $tenantToken,
            'Content-Type'   => 'application/json',
            'Accept'         => 'application/json',
        ])->patch("{$this->baseUrl}/tenant/channels/{$slug}", [
            'name' => $name,
        ]);

        if ($response->failed()) {
            throw new VersaAtendeException('Falha ao atualizar canal: ' . $response->json('message', 'Erro desconhecido.'));
        }

        // Resposta esperada: { "success": true, "channel": { slug, name, apiKey } }
        $body = $response->json();

        return $body['channel'] ?? $body ?? [];
    }

    /**
     * Remove um canal pelo seu slug.
     * Endpoint: DELETE /tenant/channels/{slug}
     */
    public function delete(string $tenantToken, string $slug): void
    {
        $response = Http::withHeaders([
            'x-tenant-token' => $tenantToken,
            'Content-Type'   => 'application/json',
            'Accept'         => 'application/json',
        ])->delete("{$this->baseUrl}/tenant/channels/{$slug}");

        if ($response->failed()) {
            throw new VersaAtendeException('Falha ao excluir canal: ' . $response->json('message', 'Erro desconhecido.'));
        }
    }
}
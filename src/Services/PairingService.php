<?php

namespace Versa\VersaAtende\Services;

use Illuminate\Support\Facades\Http;
use Versa\VersaAtende\Exceptions\VersaAtendeException;

class PairingService
{
    public function __construct(
        protected string $baseUrl,
        protected string $adminKey
    ) {}

    /**
     * Valida o código de pareamento e vincula a TV ao canal.
     * Endpoint: POST /admin/pairing/validate
     * Auth: x-admin-key + x-tenant-token
     *
     * @param string $code Código de 6 dígitos exibido na TV
     * @param string $channelSlug Slug do canal a ser pareado
     * @param string $tenantToken Token do tenant
     * @return array Resposta da API
     * @throws VersaAtendeException
     */
    public function pairTv(string $code, string $channelSlug, string $tenantToken): array
    {
        $response = Http::withHeaders([
            'x-admin-key'    => $this->adminKey,
            'x-tenant-token' => $tenantToken,
            'Content-Type'   => 'application/json',
            'Accept'         => 'application/json',
        ])->post("{$this->baseUrl}/admin/pairing/validate", [
            'code'        => $code,
            'channelSlug' => $channelSlug,
        ]);

        $body = $response->json();

        // A API pode retornar HTTP 200 com success:false (código inválido/expirado)
        // ou HTTP 4xx/5xx para falhas de infra. Ambos devem lançar exceção.
        if ($response->failed() || empty($body['success'])) {
            $mensagemErro = $body['error'] ?? $body['message'] ?? 'Erro desconhecido.';
            throw new VersaAtendeException('Falha ao parear TV: ' . $mensagemErro);
        }

        // Resposta esperada: { "success": true, "message": "Pareamento realizado com sucesso" }
        return $body;
    }
}

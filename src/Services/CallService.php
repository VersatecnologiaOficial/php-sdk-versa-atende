<?php

namespace Versa\VersaAtende\Services;

use Illuminate\Support\Facades\Http;
use Versa\VersaAtende\Exceptions\VersaAtendeException;

class CallService
{
    public function __construct(
        protected string $baseUrl
    ) {}

    /**
     * Envia uma chamada de paciente para ser exibida no painel da TV.
     * Endpoint: POST /chamada
     * Auth: x-auth-token (apiKey do canal) + x-channel-id (slug do canal)
     *
     * @param string $apiKey     Token de autenticação do canal (obtido na criação do canal)
     * @param string $channelSlug  Slug do canal de destino (x-channel-id)
     * @param array  $callData   Payload da chamada (formato Versa ou NovoSGA)
     * @return array Resposta da API
     * @throws VersaAtendeException
     */
    public function send(string $apiKey, string $channelSlug, array $callData): array
    {
        $response = Http::withHeaders([
            'x-auth-token' => $apiKey,
            'x-channel-id' => $channelSlug,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ])
        ->timeout(10)
        ->connectTimeout(5)
        ->post("{$this->baseUrl}/chamada", $callData);

        if ($response->failed()) {
            throw new VersaAtendeException(
                'Falha ao enviar chamada: ' . $response->json('message', 'Erro desconhecido.'),
                $response->status()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Monta o payload no formato Versa (simplificado) para envio de chamada.
     *
     * @param string $patientName  Nome do paciente
     * @param string $destination  Destino da chamada (ex: "Sala de Triagem")
     * @param bool   $isPriority   Se é atendimento prioritário
     * @param string $sourceSystem Nome do sistema de origem
     * @return array Payload formatado
     */
    public static function buildVersaPayload(
        string $patientName,
        string $destination,
        bool $isPriority = false,
        string $sourceSystem = 'VersaSaude'
    ): array {
        return [
            'source_system' => $sourceSystem,
            'current_call'  => [
                'patient_name' => $patientName,
                'destination'  => $destination,
                'is_priority'  => $isPriority,
            ],
        ];
    }
}

<?php

namespace Versa\VersaAtende;

use Versa\VersaAtende\Services\ChannelService;
use Versa\VersaAtende\Services\PairingService;
use Versa\VersaAtende\Services\TenantService;

class VersaAtende
{
    public function __construct(
        protected string $baseUrl,
        protected string $adminKey
    ) {}

    /**
     * Gerenciamento de Tenants e Ativação de Integrações.
     */
    public function tenants(): TenantService
    {
        return new TenantService($this->baseUrl, $this->adminKey);
    }

    /**
     * Gerenciamento de Canais de Chamada.
     */
    public function channels(): ChannelService
    {
        return new ChannelService($this->baseUrl);
    }

    /**
     * Pareamento de TV com Canais.
     */
    public function pairing(): PairingService
    {
        return new PairingService($this->baseUrl, $this->adminKey);
    }
}
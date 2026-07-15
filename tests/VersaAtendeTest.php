<?php

namespace Versa\VersaAtende\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Versa\VersaAtende\Contracts\VersaAtendeConfigurable;
use Versa\VersaAtende\Exceptions\VersaAtendeException;
use Versa\VersaAtende\Traits\HasVersaAtende;
use Versa\VersaAtende\VersaAtende;

/**
 * Modelo Fictício (Dummy) criado exclusivamente para testar a relação polimórfica.
 * Simula como um projeto real (ex: versa-indicadores) usaria o SDK.
 */
class DummyModel extends Model implements VersaAtendeConfigurable
{
    use HasVersaAtende;

    protected $table = 'dummies';
    protected $guarded = [];
    public $timestamps = false;
}

class VersaAtendeTest extends TestCase
{
    use RefreshDatabase;

    private VersaAtende $versaAtende;
    private string $apiBaseUrl = 'https://api.versa-atende.test';
    private string $adminKey = 'secret-key-123';

    protected function setUp(): void
    {
        parent::setUp();

        $this->versaAtende = new VersaAtende($this->apiBaseUrl, $this->adminKey);
        $this->artisan('migrate', ['--database' => 'testing'])->run();

        Schema::create('dummies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
    }

    /**
     * @test
     * @throws VersaAtendeException
     */
    public function create_tenant_faz_requisicao_http_correta_e_retorna_dados(): void
    {
        Http::fake([
            "{$this->apiBaseUrl}/admin/tenants" => Http::response([
                'tenant' => [
                    'id' => 'tenant_12345',
                    'apiToken' => 'token_abc',
                ]
            ], 201)
        ]);

        $response = $this->versaAtende->createTenant('Prefeitura Teste', 'prefeitura-teste');

        $this->assertEquals('tenant_12345', $response['tenant']['id']);

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('x-admin-key', $this->adminKey) &&
                $request['name'] === 'Prefeitura Teste' &&
                $request['slug'] === 'prefeitura-teste';
        });
    }

    public function create_tenant_lanca_excecao_quando_api_retorna_erro(): void
    {
        Http::fake([
            '*' => Http::response(['message' => 'Erro interno do servidor'], 500)
        ]);

        $this->expectException(VersaAtendeException::class);
        $this->expectExceptionMessage('Falha na comunicação com o Versa Atende: Erro interno do servidor');

        $this->versaAtende->createTenant('Vai Falhar', 'vai-falhar');
    }

    /**
     * @test
     * @throws VersaAtendeException
     */
    public function activate_integration_salva_os_dados_na_tabela_polimorfica_corretamente(): void
    {
        Http::fake([
            '*' => Http::response([
                'tenant' => [
                    'id'           => 'tenant_999',
                    'apiToken'     => 'super-secret-token',
                    'x-auth-token' => 'auth-123'
                ]
            ], 201)
        ]);

        $dummyModel = DummyModel::create(['name' => 'Nova Cidade']);

        $this->versaAtende->activateIntegration($dummyModel, 'Nova Cidade', 'nova-cidade');
        $this->assertDatabaseHas('versa_atende_tenants', [
            'model_type'   => DummyModel::class,
            'model_id'     => $dummyModel->id,
            'tenant_id'    => 'tenant_999',
            'tenant_slug'  => 'nova-cidade',
            'tenant_token' => 'super-secret-token',
            'is_active'    => true
        ]);
    }

    /**
     * @test
     */
    public function activate_integration_bloqueia_salvamento_se_dados_da_api_estiverem_incompletos(): void
    {
        Http::fake([
            '*' => Http::response([
                'tenant' => [
                    'id' => 'tenant_999'
                ]
            ], 201)
        ]);

        $dummyModel = DummyModel::create(['name' => 'Cidade Incompleta']);

        $this->expectException(VersaAtendeException::class);
        $this->expectExceptionMessage('A resposta da API não retornou as credenciais obrigatórias (id ou apiToken).');

        $this->versaAtende->activateIntegration($dummyModel, 'Cidade Incompleta', 'cidade-incompleta');
        $this->assertDatabaseCount('versa_atende_tenants', 0);
    }
}
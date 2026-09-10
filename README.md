# Versa Atende SDK

SDK para integração rápida com a API do Versa Atende utilizando relações polimórficas.

## Instalação

Adicione o repositório local no seu composer.json e rode:
`composer require versa-saude/versa-atende-sdk`

## Configuração
1. Publique as configurações (Opcional):
   `php artisan vendor:publish --tag=versa-atende-config`.
2. Adicione as variáveis no `.env`: `VERSA_ATENDE_BASE_URL="https://sua-url-aqui"` e `VERSA_ATENDE_X_ADMIN_KEY="sua-chave-aqui"`.
3. Rode as migrations (as tabelas do pacote são carregadas automaticamente): `php artisan migrate`

## Uso
### 1. Preparando a Model 
#### Adicione a interface VersaAtendeConfigurable e a trait HasVersaAtende ao seu Model:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Versa\VersaAtende\Contracts\VersaAtendeConfigurable;
use Versa\VersaAtende\Traits\HasVersaAtende;

class Clinica extends Model implements VersaAtendeConfigurable 
{
    use HasVersaAtende;
    
    // ...
}
```
### 2. Ativando a Integração
#### Utilize a Facade do pacote. O método activateIntegration fará o POST na API externa e salvará as chaves de acesso na relação polimórfica da model instantaneamente:

```php
use Versa\VersaAtende\Facades\VersaAtende;
use Versa\VersaAtende\Exceptions\VersaAtendeException;

$clinica = Clinica::find(1);

try {
    VersaAtende::activateIntegration($clinica, $clinica->nome, $clinica->slug);
    // Sucesso! As chaves (tenant_id, tenant_token) já estão no banco de dados.
} catch (VersaAtendeException $e) {
    // Trate a falha de comunicação com a API
}
```


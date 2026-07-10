<?php

namespace Versa\VersaAtende\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VersaAtendeTenant extends Model
{
    protected $table = 'versa_atende_tenants';

    protected $fillable = [
        'model_type',
        'model_id',
        'tenant_id',
        'tenant_slug',
        'tenant_token',
        'auth_token',
        'ativado'
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
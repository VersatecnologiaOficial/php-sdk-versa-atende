<?php

namespace Versa\VersaAtende\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Versa\VersaAtende\Database\Factories\VersaAtendeTenantFactory;

class VersaAtendeTenant extends Model
{
    use HasFactory;

    protected $table = 'versa_atende_tenants';

    protected $fillable = [
        'model_type',
        'model_id',
        'tenant_id',
        'tenant_slug',
        'tenant_token',
        'auth_token',
        'is_active'
    ];

    protected static function newFactory(): VersaAtendeTenantFactory
    {
        return VersaAtendeTenantFactory::new();
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public static function isActiveForModel(string $modelClass, int $modelId): bool
    {
        return self::where('model_type', $modelClass)
            ->where('model_id', $modelId)
            ->where('is_active', true)
            ->exists();
    }
}
<?php

namespace Versa\VersaAtende\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Versa\VersaAtende\Models\VersaAtendeTenant;

class VersaAtendeTenantFactory extends Factory
{
    protected $model = VersaAtendeTenant::class;

    public function definition(): array
    {
        return [
            'model_type'    => 'App\\Models\\ModelGenerico',
            'model_id'      => $this->faker->randomNumber(4),
            'tenant_id'     => Str::random(10),
            'tenant_slug'   => Str::slug($this->faker->unique()->city()),
            'tenant_token'  => Str::random(40),
            'auth_token'    => Str::random(40),
            'is_active'     => $this->faker->boolean(),
        ];
    }

    public function active(): self
    {
        return $this->state(function () {
            return [
                'is_active' => true,
            ];
        });
    }

    public function inactive(): self
    {
        return $this->state(function () {
            return [
                'is_active' => false,
            ];
        });
    }

    public function withModel(string $modelClass, int $modelId): self
    {
        return $this->state(function () use ($modelClass, $modelId) {
            return [
                'model_type' => $modelClass,
                'model_id'   => $modelId,
            ];
        });
    }
}
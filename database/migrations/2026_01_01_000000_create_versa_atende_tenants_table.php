<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('versa_atende_tenants', function (Blueprint $table) {
            $table->id();

            $table->morphs('model');
            $table->unique(['model_type', 'model_id']);

            $table->string('tenant_id')->nullable();
            $table->string('tenant_slug')->nullable();
            $table->string('tenant_token')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versa_atende_tenants');
    }
};
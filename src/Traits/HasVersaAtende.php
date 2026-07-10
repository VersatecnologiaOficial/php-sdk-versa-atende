<?php

namespace Versa\VersaAtende\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Versa\VersaAtende\Models\VersaAtendeTenant;

trait HasVersaAtende
{
    public function versaAtendeConfig(): MorphOne
    {
        return $this->morphOne(VersaAtendeTenant::class, 'model');
    }
}
<?php

namespace Versa\VersaAtende\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface VersaAtendeConfigurable
{
    public function versaAtendeConfig(): MorphOne;
}
<?php

namespace Versa\VersaAtende\Facades;

use Illuminate\Support\Facades\Facade;

class VersaAtende extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'versa-atende';
    }
}
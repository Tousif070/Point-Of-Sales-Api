<?php

namespace App\Services\CustomerAccountStatement;

use Illuminate\Support\Facades\Facade;

class CustomerAccountStatementFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'CustomerAccountStatement';
    }
}
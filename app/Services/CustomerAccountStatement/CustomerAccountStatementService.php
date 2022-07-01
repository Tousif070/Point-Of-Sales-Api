<?php

namespace App\Services\CustomerAccountStatement;

use App\Services\CustomerAccountStatement\CustomerAccountStatement;

class CustomerAccountStatementService
{
    public static function Handle($app)
    {
        $app->bind('CustomerAccountStatement', function() {

            return new CustomerAccountStatement();

        });
    }
}
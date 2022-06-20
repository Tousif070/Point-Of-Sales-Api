<?php

namespace App\Services\MoneyTransaction;

class SalePayment implements MoneyTransactionContract
{
    public function finalize($request)
    {
        return response(['message' => 'Sale Payment Complete !'], 200);
    }
}
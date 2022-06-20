<?php

namespace App\Services\MoneyTransaction;

class PurchasePayment implements MoneyTransactionContract
{
    public function finalize($request)
    {
        return response(['message' => 'Purchase Payment Complete !'], 200);
    }
}
<?php

namespace App\Services\MoneyTransaction;

interface MoneyTransactionContract
{
    public function finalize($request);
}
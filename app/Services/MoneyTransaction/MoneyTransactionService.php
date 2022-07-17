<?php

namespace App\Services\MoneyTransaction;

use App\Services\MoneyTransaction\SalePayment;
use App\Services\MoneyTransaction\CollectiveSalePayment;
use App\Services\MoneyTransaction\PurchasePayment;

class MoneyTransactionService
{
    public static function Handle($app)
    {
        $app->bind('MoneyTransaction', function() {
            
            if(request()->money_transaction_type == "sale")
            {
                return new SalePayment();
            }
            else if(request()->money_transaction_type == "sale_collective")
            {
                return new CollectiveSalePayment();
            }
            else if(request()->money_transaction_type == "purchase")
            {
                return new PurchasePayment();
            }
            else
            {
                return null;
            }

        });
    }
}
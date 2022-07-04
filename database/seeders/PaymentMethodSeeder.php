<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payment_method = PaymentMethod::where('name', '=', 'CUSTOMER CREDIT')->first();

        if($payment_method == null)
        {
            $payment_method = new PaymentMethod();

            $payment_method->name = "CUSTOMER CREDIT";

            $payment_method->save();
        }
    }
}

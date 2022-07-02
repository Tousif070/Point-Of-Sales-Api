<?php

namespace App\Services\CustomerAccountStatement;

use App\Models\CustomerAccountStatement as CAS;
use Carbon\Carbon;

class CustomerAccountStatement
{
    public function get($customer_id)
    {
        return $customer_id;
    }

    public function setInitialOpeningBalance($customer_id)
    {
        $cas = new CAS();

        $cas->type = "Opening Balance";

        $cas->amount = 0.00;

        $cas->customer_id = $customer_id;

        $cas->save();


        $date = Carbon::today();

        $cas->created_at = $date->year . "-" . $date->month . "-01";

        $cas->save();
    }

    public function openingBalanceForThisMonth()
    {
        $date = Carbon::today();

        $start_date_of_this_month = $date->year . "-" . $date->month . "-01";

        $opening_balance = CAS::where('type', '=', 'Opening Balance')
            ->whereDate('created_at', '=', $start_date_of_this_month)
            ->get();
        
        if(count($opening_balance) > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function setClosingBalanceForPreviousMonth($customer_id)
    {
        $date = Carbon::today()->subMonth();

        $start = $date->year . "-" . $date->month . "-01";
        
        $end = $date->year . "-" . $date->month . "-" . $date->daysInMonth . " 23:59:58";

        $overall_balance = CAS::whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->sum('amount');
            
        $closing_balance = new CAS();
        
        $closing_balance->type = "Closing Balance";

        $closing_balance->amount = $overall_balance;

        $closing_balance->customer_id = $customer_id;
        
        $closing_balance->save();

        
        $closing_balance->created_at = $end;
        
        $closing_balance->save();


        // ASSIGNING THE CLOSING BALANCE OF THE PREVIOUS MONTH TO THIS MONTH'S OPENING BALANCE
        $this->setOpeningBalanceForThisMonth($overall_balance, $customer_id);
    }

    public function setOpeningBalanceForThisMonth($amount, $customer_id)
    {
        $opening_balance = new CAS();
        
        $opening_balance->type = "Opening Balance";

        $opening_balance->amount = $amount;

        $opening_balance->customer_id = $customer_id;
        
        $opening_balance->save();

        
        $date = Carbon::today();
        
        $opening_balance->created_at = $date->year . "-" . $date->month . "-01";
        
        $opening_balance->save();
    }

    public function store($cas_data_arr)
    {
        if(!$this->openingBalanceForThisMonth())
        {
            $this->setClosingBalanceForPreviousMonth($cas_data_arr['customer_id']);
        }

        $cas = new CAS();

        $cas->type = $cas_data_arr['type'];

        $cas->reference_id = $cas_data_arr['reference_id'];

        $cas->amount = $cas_data_arr['amount'];

        $cas->customer_id = $cas_data_arr['customer_id'];

        $cas->save();
    }


}
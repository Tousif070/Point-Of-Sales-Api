<?php

namespace App\Services\CustomerAccountStatement;

use App\Models\CustomerAccountStatement as CAS;
use App\Models\SaleReturnTransaction;
use DB;
use Carbon\Carbon;

class CustomerAccountStatement
{
    public function get($customer_id)
    {
        $statements = CAS::leftJoin('sale_transactions as st', 'st.id', '=', 'customer_account_statements.reference_id')
            ->leftJoin('payments as p', 'p.id', '=', 'customer_account_statements.reference_id')
            ->leftJoin('sale_return_transactions as srt', 'srt.id', '=', 'customer_account_statements.reference_id')
            ->leftJoin('payment_methods as pm', 'pm.id', '=', 'p.payment_method_id')
            ->select(

                'customer_account_statements.id',
                DB::raw('DATE_FORMAT(customer_account_statements.created_at, "%m/%d/%Y") as date'),
                'customer_account_statements.type',
                'customer_account_statements.reference_id',
                DB::raw('(CASE
                
                    WHEN customer_account_statements.type = "Invoice" THEN st.invoice_no
                    WHEN customer_account_statements.type = "Payment" THEN CONCAT_WS("-", (select invoice_no from sale_transactions where id = p.transaction_id), p.payment_no)
                    WHEN customer_account_statements.type = "Return" THEN CONCAT_WS("-", (select invoice_no from sale_transactions where id = srt.sale_transaction_id), srt.invoice_no)
                    ELSE "-"
                
                END) as reference'),
                DB::raw('IF(customer_account_statements.type = "Payment", pm.name, "-") as payment_method'),
                'customer_account_statements.amount'

            )->where('customer_account_statements.customer_id', '=', $customer_id)
            ->orderBy('customer_account_statements.created_at', 'asc')
            ->get();
        

        $balance = 0;

        foreach($statements as $row)
        {
            if($row->type == "Opening Balance" || $row->type == "Closing Balance")
            {
                $balance = $row->amount;

                $row->balance = $balance;
            }
            else if($row->type == "Invoice" || $row->type == "Payment")
            {
                $balance += $row->amount;

                $row->balance = $balance;
            }
            else if($row->type == "Return")
            {
                $srt = SaleReturnTransaction::find($row->reference_id);

                $amount_adjusted = $srt->amount - $srt->amount_credited;

                $balance -= $amount_adjusted;

                $row->amount_credited = $srt->amount_credited;
                
                $row->amount_adjusted = $amount_adjusted;
                
                $row->balance = $balance;
            }
        }

        return $statements;
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

    public function checkOpeningBalance($date, $customer_id)
    {
        $start = $date->year . "-" . $date->month . "-01";

        $opening_balance = CAS::where('type', '=', 'Opening Balance')
            ->whereDate('created_at', '=', $start)
            ->where('customer_id', '=', $customer_id)
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

    public function setOpeningAndClosingBalance($date, $customer_id)
    {
        $start = $date->year . "-" . $date->month . "-01";
        
        $end = $date->year . "-" . $date->month . "-" . $date->daysInMonth . " 23:59:58";

        $overall_balance = 0;

        if(!$this->checkOpeningBalance($date, $customer_id))
        {
            $overall_balance = $this->setOpeningAndClosingBalance($date->copy()->subMonth(), $customer_id);
        }
        else
        {
            $statements = CAS::whereDate('created_at', '>=', $start)
                ->whereDate('created_at', '<=', $end)
                ->where('customer_id', '=', $customer_id)
                ->get();

            foreach($statements as $row)
            {
                if($row->type != "Return")
                {
                    $overall_balance += $row->amount;
                }
                else
                {
                    $srt = SaleReturnTransaction::find($row->reference_id);

                    $amount_adjusted = $srt->amount - $srt->amount_credited;

                    $overall_balance -= $amount_adjusted;
                }
            }
        }

        // CLOSING BALANCE FOR CURRENT MONTH
        $closing_balance = new CAS();
        
        $closing_balance->type = "Closing Balance";

        $closing_balance->amount = $overall_balance;

        $closing_balance->customer_id = $customer_id;
        
        $closing_balance->save();

        
        $closing_balance->created_at = $end;
        
        $closing_balance->save();


        // OPENING BALANCE FOR NEXT MONTH
        $opening_balance = new CAS();
        
        $opening_balance->type = "Opening Balance";

        $opening_balance->amount = $overall_balance;

        $opening_balance->customer_id = $customer_id;
        
        $opening_balance->save();

        
        $date = $date->addMonth();
        
        $opening_balance->created_at = $date->year . "-" . $date->month . "-01";
        
        $opening_balance->save();

        
        return $overall_balance;
    }

    public function store($cas_data_arr)
    {
        $date = Carbon::today();

        if(!$this->checkOpeningBalance($date, $cas_data_arr['customer_id']))
        {
            $this->setOpeningAndClosingBalance($date->copy()->subMonth(), $cas_data_arr['customer_id']);
        }

        $cas = new CAS();

        $cas->type = $cas_data_arr['type'];

        $cas->reference_id = $cas_data_arr['reference_id'];

        $cas->amount = $cas_data_arr['amount'];

        $cas->customer_id = $cas_data_arr['customer_id'];

        $cas->save();
    }


}
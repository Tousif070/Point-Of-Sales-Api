<?php

namespace App\Services\MoneyTransaction;

use App\Models\SaleTransaction;
use App\Models\Payment;
use App\Models\CustomerCredit;
use App\Models\User;
use CAS;
use REC;
use DB;
use Exception;
use Carbon\Carbon;

class CollectiveSalePayment implements MoneyTransactionContract
{
    public function finalize($request)
    {
        if(!auth()->user()->hasPermission("money-transaction.sale-payment"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'customer_id' => 'required | numeric',
            'amount' => 'required | numeric | min:1',
            'payment_date' => 'required | date',
            'payment_method_id' => 'required | numeric',
            'payment_note' => 'string | nullable'
        ], [
            'customer_id.required' => 'Customer ID is required !',
            'customer_id.numeric' => 'Customer ID should be numeric !',

            'amount.required' => 'Amount is required !',
            'amount.numeric' => 'Amount should be numeric !',
            'amount.min' => 'Amount cannot be less than 1 !',

            'payment_date.required' => 'Please specify the payment date !',
            'payment_date.date' => 'Please specify a valid date !',

            'payment_method_id.required' => 'Please select the payment method !',
            'payment_method_id.numeric' => 'Payment Method ID should be numeric !',

            'payment_note.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !'
        ]);


        // CHECKING IF PAYMENT METHOD IS CUSTOMER CREDIT OR NOT
        if($request->payment_method_id == 1)
        {
            $customer = User::where('id', '=', $request->customer_id)->where('type', '=', 2)->first();

            $available_credit = $customer->userDetail->available_credit;

            if($available_credit >= $request->amount)
            {
                $customer->userDetail->available_credit -= $request->amount;

                $customer->userDetail->save();
            }
            else
            {
                return response([
                    'errors' => [
                        'payment_method_id' => ['Insufficient Customer Credit !']
                    ]
                ], 409);
            }
        }


        $sale_transactions = SaleTransaction::leftJoin('payments as p', function($query) {

            $query->on('p.transaction_id', '=', 'sale_transactions.id')
                ->where('p.payment_for', '=', 'sale');

        })
        ->select(
            
            'sale_transactions.id',
            DB::raw('
            
            sale_transactions.amount - IFNULL((select SUM(amount) from sale_return_transactions where sale_transaction_id = sale_transactions.id), 0)
            - IFNULL(SUM(p.amount), 0)
            
            as due')

        )->where('sale_transactions.status', '=', 'Final')  
        ->where('sale_transactions.customer_id', '=', $request->customer_id)
        ->where('sale_transactions.payment_status', '<>', 'Paid')
        ->groupBy('sale_transactions.id')
        ->orderBy('due', 'asc')
        ->get();


        $flag = true;

        $prefix = "";

        $amount = 0;


        DB::beginTransaction();

        try {

            foreach($sale_transactions as $row)
            {
                $sale_transaction = SaleTransaction::find($row->id);

                if($request->amount < 1)
                {
                    break;
                }
                else if($request->amount >= $row->due)
                {
                    $amount = $row->due;
                    $request->amount -= $row->due;

                    $sale_transaction->payment_status = "Paid";        
                    $sale_transaction->save();
                }
                else if($request->amount < $row->due)
                {                    
                    $amount = $request->amount;
                    $request->amount -= $row->due;
        
                    $sale_transaction->payment_status = "Partial";
                    $sale_transaction->save();
                }
                                
                    
                $payment = new Payment();
    
                $payment->payment_for = "sale";
    
                $payment->transaction_id = $sale_transaction->id;
    
                $payment->amount = $amount;
    
                $payment->payment_date = Carbon::parse($request->payment_date);
    
                $payment->payment_method_id = $request->payment_method_id;
    
                $payment->payment_note = $request->payment_note;
    
                $payment->finalized_by = auth()->user()->id;
    
                $payment->finalized_at = Carbon::now();
    
                $payment->save();


                if($flag)
                {
                    $prefix = $payment->id + 100;

                    $flag = false;
                }
    
    
                $payment->payment_no = "G" . $prefix . ":SP#" . ($payment->id + 1000);
    
                $payment->save();
    
    
                // SALE PAYMENT ENTRY FOR CUSTOMER ACCOUNT STATEMENT
                $cas_data_arr = [
                    'type' => 'Payment',
                    'reference_id' => $payment->id,
                    'amount' => ($payment->amount * (-1)),
                    'customer_id' => $sale_transaction->customer_id
                ];
    
                CAS::store($cas_data_arr);


                // RECORD ENTRY FOR SALE PAYMENT
                $rec_data_arr = [
                    'category' => 'Money',
                    'type' => 'Sale',
                    'reference_id' => $payment->id,
                    'cash_flow' => 'in',
                    'amount' => $payment->amount
                ];

                REC::store($rec_data_arr);

            }


            if($request->amount > 0 && $request->payment_method_id != 1)
            {
                $this->storeCustomerCredit($request->amount, $request->customer_id);
            }


            DB::commit();

            return response(['message' => 'Collective Sale Payment Complete !'], 200);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }

    public function storeCustomerCredit($amount, $customer_id)
    {  
        $customer = User::where('id', '=', $customer_id)->where('type', '=', 2)->first();

        
        $customer_credit = new CustomerCredit();

        $customer_credit->amount = $amount;

        $customer_credit->type = "Over Payment";

        $customer_credit->customer_id = $customer_id;

        $customer_credit->note = "From over payment";

        $customer_credit->finalized_by = auth()->user()->id;

        $customer_credit->finalized_at = Carbon::now();

        $customer_credit->save();


        $customer->userDetail->available_credit += $amount;

        $customer->userDetail->save();


        // RECORD ENTRY FOR CUSTOMER CREDIT
        $rec_data_arr = [
            'category' => 'Money',
            'type' => 'Add CC',
            'reference_id' => $customer_credit->id,
            'cash_flow' => 'in',
            'amount' => $customer_credit->amount
        ];

        REC::store($rec_data_arr);
    }


    // public function finalize($request)
    // {
    //     if(!auth()->user()->hasPermission("money-transaction.sale-payment"))
    //     {
    //         return response(['message' => 'Permission Denied !'], 403);
    //     }

    //     $request->validate([
    //         'transaction_ids' => 'required | array',
    //         'customer_id' => 'required | numeric',
    //         'amount' => 'required | numeric',
    //         'payment_date' => 'required | date',
    //         'payment_method_id' => 'required | numeric',
    //         'payment_note' => 'string | nullable'
    //     ], [
    //         'transaction_ids.required' => 'Please specify the transactions !',
    //         'transaction_ids.array' => 'Transaction IDs should be in an array !',

    //         'customer_id.required' => 'Customer ID is required !',
    //         'customer_id.numeric' => 'Customer ID should be numeric !',

    //         'amount.required' => 'Amount is required !',
    //         'amount.numeric' => 'Amount should be numeric !',

    //         'payment_date.required' => 'Please specify the payment date !',
    //         'payment_date.date' => 'Please specify a valid date !',

    //         'payment_method_id.required' => 'Please select the payment method !',
    //         'payment_method_id.numeric' => 'Payment Method ID should be numeric !',

    //         'payment_note.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !'
    //     ]);


    //     // CHECKING IF PAYMENT METHOD IS CUSTOMER CREDIT OR NOT
    //     if($request->payment_method_id == 1)
    //     {
    //         $customer = User::where('id', '=', $request->customer_id)->where('type', '=', 2)->first();

    //         $available_credit = $customer->userDetail->available_credit;

    //         if($available_credit >= $request->amount)
    //         {
    //             $customer->userDetail->available_credit -= $request->amount;

    //             $customer->userDetail->save();
    //         }
    //         else
    //         {
    //             return response([
    //                 'errors' => [
    //                     'payment_method_id' => ['Insufficient Customer Credit !']
    //                 ]
    //             ], 409);
    //         }
    //     }


    //     $flag = true;

    //     $prefix = "";


    //     DB::beginTransaction();

    //     try {

    //         foreach($request->transaction_ids as $transaction_id)
    //         {
                
    //             // CALCULATING THE DUE AMOUNT
    //             $sale_transaction = SaleTransaction::find($transaction_id);
                
    //             $due = $sale_transaction->amount - $sale_transaction->payments()->where('payment_for', '=', 'sale')->sum('amount') - $sale_transaction->saleReturnTransactions->sum('amount');
    
    
    //             $payment = new Payment();
    
    //             $payment->payment_for = "sale";
    
    //             $payment->transaction_id = $sale_transaction->id;
    
    //             $payment->amount = $due;
    
    //             $payment->payment_date = Carbon::parse($request->payment_date);
    
    //             $payment->payment_method_id = $request->payment_method_id;
    
    //             $payment->payment_note = $request->payment_note;
    
    //             $payment->finalized_by = auth()->user()->id;
    
    //             $payment->finalized_at = Carbon::now();
    
    //             $payment->save();


    //             if($flag)
    //             {
    //                 $prefix = $payment->id + 100;

    //                 $flag = false;
    //             }
    
    
    //             $payment->payment_no = "G" . $prefix . ":SP#" . ($payment->id + 1000);
    
    //             $payment->save();
    
    
    //             $sale_transaction->payment_status = "Paid";
    
    //             $sale_transaction->save();
    
    
    //             // SALE PAYMENT ENTRY FOR CUSTOMER ACCOUNT STATEMENT
    //             $cas_data_arr = [
    //                 'type' => 'Payment',
    //                 'reference_id' => $payment->id,
    //                 'amount' => ($payment->amount * (-1)),
    //                 'customer_id' => $sale_transaction->customer_id
    //             ];
    
    //             CAS::store($cas_data_arr);


    //             // RECORD ENTRY FOR SALE PAYMENT
    //             $rec_data_arr = [
    //                 'category' => 'Money',
    //                 'type' => 'Sale',
    //                 'reference_id' => $payment->id,
    //                 'cash_flow' => 'in',
    //                 'amount' => $payment->amount
    //             ];

    //             REC::store($rec_data_arr);

    //         }


    //         DB::commit();

    //         return response(['message' => 'Collective Sale Payment Complete !'], 200);

    //     } catch(Exception $ex) {

    //         DB::rollBack();

    //         return response([
    //             'message' => 'Internal Server Error !',
    //             'error' => $ex->getMessage()
    //         ], 500);

    //     }
    // }


}
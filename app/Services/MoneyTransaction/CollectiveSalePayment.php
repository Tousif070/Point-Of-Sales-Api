<?php

namespace App\Services\MoneyTransaction;

use App\Models\SaleTransaction;
use App\Models\Payment;
use App\Models\User;
use CAS;
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
            'transaction_ids' => 'required | array',
            'customer_id' => 'required | numeric',
            'amount' => 'required | numeric',
            'payment_date' => 'required | date',
            'payment_method_id' => 'required | numeric',
            'payment_note' => 'string | nullable'
        ], [
            'transaction_ids.required' => 'Please specify the transactions !',
            'transaction_ids.array' => 'Transaction IDs should be in an array !',

            'customer_id.required' => 'Customer ID is required !',
            'customer_id.numeric' => 'Customer ID should be numeric !',

            'amount.required' => 'Amount is required !',
            'amount.numeric' => 'Amount should be numeric !',

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


        $flag = true;

        $prefix = "";


        DB::beginTransaction();

        try {

            foreach($request->transaction_ids as $transaction_id)
            {
                
                // CALCULATING THE DUE AMOUNT
                $sale_transaction = SaleTransaction::find($transaction_id);
                
                $due = $sale_transaction->amount - $sale_transaction->payments()->where('payment_for', '=', 'sale')->sum('amount') - $sale_transaction->saleReturnTransactions->sum('amount');
    
    
                $payment = new Payment();
    
                $payment->payment_for = "sale";
    
                $payment->transaction_id = $sale_transaction->id;
    
                $payment->amount = $due;
    
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
    
    
                $payment->payment_no = "G#" . $prefix . "|SP#" . ($payment->id + 1000);
    
                $payment->save();
    
    
                $total_paid = $sale_transaction->payments()->where('payment_for', '=', 'sale')->sum('amount');
    
                $total_payable = $sale_transaction->amount - $sale_transaction->saleReturnTransactions->sum('amount');
    
                if($total_paid == $total_payable)
                {
                    $sale_transaction->payment_status = "Paid";
                }
    
                $sale_transaction->save();
    
    
                // SALE PAYMENT ENTRY FOR CUSTOMER ACCOUNT STATEMENT
                $cas_data_arr = [
                    'type' => 'Payment',
                    'reference_id' => $payment->id,
                    'amount' => ($payment->amount * (-1)),
                    'customer_id' => $sale_transaction->customer_id
                ];
    
                CAS::store($cas_data_arr);

            }


            DB::commit();

            return response(['message' => 'Sale Payment Complete !'], 200);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }
}
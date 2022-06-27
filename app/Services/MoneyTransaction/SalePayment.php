<?php

namespace App\Services\MoneyTransaction;

use App\Models\SaleTransaction;
use App\Models\Payment;
use DB;
use Exception;
use Carbon\Carbon;

class SalePayment implements MoneyTransactionContract
{
    public function finalize($request)
    {
        if(!auth()->user()->hasPermission("money-transaction.sale-payment"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            // 'payment_for' => 'required | string', NOT NEEDED FOR NOW
            'transaction_id' => 'required | numeric',
            'amount' => 'required | numeric',
            'payment_date' => 'required | date',
            'payment_method_id' => 'required | numeric',
            'payment_note' => 'string | nullable'
        ], [
            // THE FOLLOWING IS NOT NEEDED FOR NOW
            // 'payment_for.required' => 'Payment for is required !',
            // 'payment_for.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'transaction_id.required' => 'Please specify the transaction !',
            'transaction_id.numeric' => 'Transaction ID should be numeric !',

            'amount.required' => 'Amount is required !',
            'amount.numeric' => 'Amount should be numeric !',

            'payment_date.required' => 'Please specify the payment date !',
            'payment_date.date' => 'Please specify a valid date !',

            'payment_method_id.required' => 'Please select the payment method !',
            'payment_method_id.numeric' => 'Payment Method ID should be numeric !',

            'payment_note.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !'
        ]);


        // CALCULATING THE DUE AMOUNT
        $sale_transaction = SaleTransaction::find($request->transaction_id);
        
        $due = $sale_transaction->amount - $sale_transaction->payments()->where('payment_for', '=', 'sale')->sum('amount') - $sale_transaction->saleReturnTransactions->sum('amount');

        if($due < 1)
        {
            return response(['message' => 'This Sale Invoice is already paid !'], 409);
        }
        
        if($request->amount < 1 || $request->amount > $due)
        {
            return response(['message' => 'Amount cannot be less than 1 or greater than the due amount !'], 409);
        }


        DB::beginTransaction();

        try {

            $payment = new Payment();

            $payment->payment_for = "sale";

            $payment->transaction_id = $request->transaction_id;

            $payment->amount = $request->amount;

            $payment->payment_date = Carbon::parse($request->payment_date);

            $payment->payment_method_id = $request->payment_method_id;

            $payment->payment_note = $request->payment_note;

            $payment->finalized_by = auth()->user()->id;

            $payment->finalized_at = Carbon::now();

            $payment->save();


            $total_paid = $sale_transaction->payments()->where('payment_for', '=', 'sale')->sum('amount');

            $total_payable = $sale_transaction->amount - $sale_transaction->saleReturnTransactions->sum('amount');

            if($total_paid < $total_payable)
            {
                $sale_transaction->payment_status = "Partial";
            }
            else if($total_paid == $total_payable)
            {
                $sale_transaction->payment_status = "Paid";
            }

            $sale_transaction->save();


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
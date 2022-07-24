<?php

namespace App\Services\MoneyTransaction;

use App\Models\PurchaseTransaction;
use App\Models\Payment;
use REC;
use DB;
use Exception;
use Carbon\Carbon;

class PurchasePayment implements MoneyTransactionContract
{
    public function finalize($request)
    {
        if(!auth()->user()->hasPermission("money-transaction.purchase-payment"))
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
        $purchase_transaction = PurchaseTransaction::find($request->transaction_id);
        
        $due = $purchase_transaction->amount - $purchase_transaction->payments()->where('payment_for', '=', 'purchase')->sum('amount');

        if($due == 0)
        {
            return response(['message' => 'This Purchase Invoice is already paid !'], 409);
        }
        
        if($request->amount < 1 || $request->amount > $due)
        {
            // return response(['message' => 'Amount cannot be less than 1 or greater than the due amount !'], 409); NOT NEEDED FOR NOW

            return response([
                'errors' => [
                    'amount' => ['Amount cannot be less than 1 or greater than the due amount !']
                ]
            ], 409);
        }


        DB::beginTransaction();

        try {

            $payment = new Payment();

            $payment->payment_for = "purchase";

            $payment->transaction_id = $request->transaction_id;

            $payment->amount = $request->amount;

            $payment->payment_date = Carbon::parse($request->payment_date);

            $payment->payment_method_id = $request->payment_method_id;

            $payment->payment_note = $request->payment_note;

            $payment->finalized_by = auth()->user()->id;

            $payment->finalized_at = Carbon::now();

            $payment->save();


            $payment->payment_no = "PP#" . ($payment->id + 1000);

            $payment->save();


            $total_paid = $purchase_transaction->payments()->where('payment_for', '=', 'purchase')->sum('amount');

            if($total_paid < $purchase_transaction->amount)
            {
                $purchase_transaction->payment_status = "Partial";
            }
            else if($total_paid == $purchase_transaction->amount)
            {
                $purchase_transaction->payment_status = "Paid";
            }

            $purchase_transaction->save();


            // RECORD ENTRY FOR PURCHASE PAYMENT
            $rec_data_arr = [
                'category' => 'Money',
                'type' => 'Purchase',
                'reference_id' => $payment->id,
                'cash_flow' => 'out',
                'amount' => $payment->amount
            ];

            REC::store($rec_data_arr);


            DB::commit();

            return response(['message' => 'Purchase Payment Complete !'], 200);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }
}
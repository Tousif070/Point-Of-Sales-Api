<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\Payment;
use DB;
use Exception;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->hasPermission("payment-method.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $payment_methods = PaymentMethod::orderBy('name', 'asc')->get();

        return response(['payment_methods' => $payment_methods], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->hasPermission("payment-method.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'name' => 'required | string | unique:payment_methods,name'
        ], [
            'name.required' => 'Please enter the name of the payment method !',
            'name.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',
            'name.unique' => 'Payment method already exists !'
        ]);

        DB::beginTransaction();

        try {

            $payment_method = new PaymentMethod();

            $payment_method->name = $request->name;

            $payment_method->save();

            DB::commit();

            return response(['payment_method' => $payment_method], 201);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function report()
    {

        $payment_report = Payment::rightJoin('payment_methods as pm', 'pm.id', '=', 'payments.payment_method_id')
        ->select(

            'pm.id',
            'pm.name',
            DB::raw('SUM(IF(payments.verification_status = 2, 1, 0)) as not_verified'),
            DB::raw('SUM(IF(payments.verification_status = 1, 1, 0)) as verified_okay'),
            DB::raw('SUM(IF(payments.verification_status = 0, 1, 0)) as verified_not_okay')

        )
        ->groupBy('pm.id')
        ->orderBy('pm.name', 'asc')
        ->get();

        return response(['payment_report' => $payment_report], 200);

    }

    public function payments($payment_method_id)
    {

        $payments = Payment::Join('users as u', 'u.id', '=', 'payments.finalized_by')
        ->leftJoin('users as u2', 'u2.id', '=', 'payments.verified_by')
        
        ->select(

            'payments.id',
            'payments.verification_status',
            'payments.verification_note',
            DB::raw('DATE_FORMAT(payments.payment_date, "%m/%d/%Y") as date'),  
            'payments.payment_no',
            DB::raw('
                CASE 
                    WHEN payment_for = "sale" THEN (SELECT invoice_no from sale_transactions where id = payments.transaction_id) 
                    WHEN payment_for = "purchase" THEN (SELECT reference_no from purchase_transactions where id = payments.transaction_id) 
                    WHEN payment_for = "expense" THEN (SELECT expense_no from expense_transactions where id = payments.transaction_id) 
                END as reference
            '),
            'payments.amount',
            'payments.payment_note',
            DB::raw('CONCAT_WS(" ", u.first_name, DATE_FORMAT(payments.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by'),
            DB::raw('CONCAT_WS(" ", u2.first_name, DATE_FORMAT(payments.verified_at, "%m/%d/%Y %H:%i:%s")) as verified_by')

        )
        ->where('payments.payment_method_id', '=', $payment_method_id)
        ->orderBy('payments.payment_date', 'desc')
        ->get();

        return response(['payments' => $payments], 200);

    }

}

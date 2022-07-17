<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\User;
use CAS;
use DB;

class ReportController extends Controller
{
    public function casIndexView()
    {
        $customers = User::select(['id', 'first_name', 'last_name'])->where('type', '=', 2)->orderBy('first_name', 'asc')->get();

        return response([
            'customers' => $customers
        ], 200);
    }

    public function casIndex(Request $request)
    {
        if(!auth()->user()->hasPermission("report.cas-index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        if(empty($request->customer_id))
        {
            return response(['statements' => []], 200);
        }

        $customer = User::where('id', '=', $request->customer_id)->where('type', '=', 2)->first();

        if($customer == null)
        {
            return response(['statements' => []], 200);
        }

        return response([
            'available_credit' => $customer->userDetail->available_credit,
            'statements' => CAS::get($request->customer_id)
        ], 200);
    }

    public function sprIndex()
    {
        if(!auth()->user()->hasPermission("report.spr-index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $payments = Payment::join('users as u', 'u.id', '=', 'payments.finalized_by')
            ->join('sale_transactions as st', 'st.id', '=', 'payments.transaction_id')
            ->join('payment_methods as pm', 'pm.id', '=', 'payments.payment_method_id')
            ->select(

                'payments.id',
                DB::raw('DATE_FORMAT(payments.payment_date, "%m/%d/%Y") as date'),
                'payments.payment_no',
                'st.invoice_no as sale_invoice',
                'payments.amount',
                'pm.name as payment_method',
                'payments.payment_note',
                DB::raw('CONCAT_WS(" ", u.first_name, DATE_FORMAT(payments.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by')

            )->where('payments.payment_for', '=', 'sale')
            ->orderBy('payments.payment_date', 'desc')
            ->get();

        return response(['payments' => $payments], 200);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Record;
use App\Models\VerificationRecord;
use App\Models\LoginLogoutRecord;
use DB;

class RecordController extends Controller
{
    public function transactions()
    {
        if(!auth()->user()->hasPermission("record.transactions"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $transactions = Record::join('users as u', 'u.id', '=', 'records.finalized_by')
            ->leftJoin('purchase_transactions as pt', 'pt.id', '=', 'records.reference_id')
            ->select(

                'records.id',
                'records.type',
                DB::raw('IF(records.type = "Purchase", pt.amount, records.amount) as amount'),
                DB::raw('CONCAT_WS(" ", u.first_name, DATE_FORMAT(records.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by')

            )->where('records.category', '=', 'Transaction')
            ->orderBy('records.created_at', 'desc')
            ->get();

        return response([
            'transaction_count' => $transactions->count('id'),
            'total_amount' => $transactions->sum('amount'),
            'transactions' => $transactions
        ], 200);
    }

    public function cashIn()
    {
        if(!auth()->user()->hasPermission("record.money"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $cash_in = Record::join('users as u', 'u.id', '=', 'records.finalized_by')
            ->select(

                'records.id',
                'records.type',
                'records.amount',
                DB::raw('CONCAT_WS(" ", u.first_name, DATE_FORMAT(records.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by')

            )->where('records.category', '=', 'Money')
            ->where('records.cash_flow', '=', 'in')
            ->orderBy('records.created_at', 'desc')
            ->get();

        return response([
            'cash_in_count' => $cash_in->count('id'),
            'total_amount' => $cash_in->sum('amount'),
            'cash_in' => $cash_in
        ], 200);
    }

    public function cashOut()
    {
        if(!auth()->user()->hasPermission("record.money"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $cash_out = Record::join('users as u', 'u.id', '=', 'records.finalized_by')
            ->select(

                'records.id',
                'records.type',
                'records.amount',
                DB::raw('CONCAT_WS(" ", u.first_name, DATE_FORMAT(records.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by')

            )->where('records.category', '=', 'Money')
            ->where('records.cash_flow', '=', 'out')
            ->orderBy('records.created_at', 'desc')
            ->get();

        return response([
            'cash_out_count' => $cash_out->count('id'),
            'total_amount' => $cash_out->sum('amount'),
            'cash_out' => $cash_out
        ], 200);
    }

    public function verificationRecord()
    {
        if(!auth()->user()->hasPermission("record.verification"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $verification_records = VerificationRecord::join('users as u', 'u.id', '=', 'verification_records.verified_by')
        ->select(

            'verification_records.id',
            'verification_records.type',
            DB::raw('
                CASE 
                    WHEN verification_records.type = "Sale" THEN (SELECT invoice_no from sale_transactions where id = verification_records.reference_id) 
                    WHEN verification_records.type = "Sale Return" THEN (SELECT invoice_no from sale_return_transactions where id = verification_records.reference_id) 
                    WHEN verification_records.type = "Purchase" THEN (SELECT reference_no from purchase_transactions where id = verification_records.reference_id) 
                    WHEN verification_records.type = "Expense" THEN (SELECT expense_no from expense_transactions where id = verification_records.reference_id) 
                    WHEN verification_records.type = "Payment" THEN (SELECT payment_no from payments where id = verification_records.reference_id) 
                END as reference
            '),
            DB::raw('CONCAT_WS(" ", u.first_name, DATE_FORMAT(verification_records.verified_at, "%m/%d/%Y %H:%i:%s")) as verified_by'),

        )
        ->orderBy('verification_records.verified_at', 'desc')
        ->get();

        return response(['verification_records' => $verification_records], 200);
    }

    public function userLog()
    {
        if(!auth()->user()->hasPermission("record.user-log"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $user_log = LoginLogoutRecord::join('users as u', 'u.id', '=', 'login_logout_records.user_id')
        ->select(

            'login_logout_records.id',
            DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as name'),
            'login_logout_records.type',
            DB::raw('DATE_FORMAT(login_logout_records.created_at, "%m/%d/%Y %H:%i:%s") as date_time')

        )
        ->where('login_logout_records.user_type', '=', 1)
        ->orderBy('login_logout_records.created_at', 'desc')
        ->get();

        return response(['user_log' => $user_log], 200);
    }


}

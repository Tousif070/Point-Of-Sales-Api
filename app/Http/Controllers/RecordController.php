<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Record;
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


}

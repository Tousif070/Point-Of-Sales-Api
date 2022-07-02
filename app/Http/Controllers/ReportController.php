<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use CAS;

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

        return response(['statements' => CAS::get($request->customer_id)], 200);
    }
}

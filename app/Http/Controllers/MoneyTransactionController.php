<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleTransaction;
use App\Models\PaymentMethod;
use App\Models\User;
use DB;

class MoneyTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $money_transaction = resolve('MoneyTransaction');

        if($money_transaction != null)
        {
            return $money_transaction->finalize($request);
        }
        else
        {
            return response(['message' => 'Something went wrong !'], 404);
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

    public function salePaymentView()
    {
        if(!auth()->user()->hasPermission("money-transaction.sale-payment"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $sale_transactions = SaleTransaction::join('users as u', 'u.id', '=', 'sale_transactions.customer_id')
            ->leftJoin('payments as p', 'p.transaction_id', '=', 'sale_transactions.id')
            ->select(

                'sale_transactions.id',
                DB::raw('DATE_FORMAT(sale_transactions.transaction_date, "%m/%d/%Y") as date'),
                'sale_transactions.invoice_no',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as customer'),
                'sale_transactions.payment_status',
                DB::raw('sale_transactions.amount - IFNULL((select SUM(amount) from sale_return_transactions where sale_transaction_id = sale_transactions.id), 0) as total_payable'),
                DB::raw('IFNULL(SUM(p.amount), 0) as paid'),
                DB::raw('
                
                sale_transactions.amount - IFNULL((select SUM(amount) from sale_return_transactions where sale_transaction_id = sale_transactions.id), 0)
                - IFNULL(SUM(p.amount), 0)
                
                as due')

            )->where('sale_transactions.payment_status', '<>', 'Paid')
            ->groupBy('sale_transactions.id')
            ->orderBy('sale_transactions.transaction_date', 'desc')
            ->get();

        return response(['sale_transactions' => $sale_transactions], 200);
    }
    
    public function makePaymentView()
    {
        $payment_methods = PaymentMethod::select(['id', 'name'])->orderBy('name', 'asc')->get();

        return response([
            'payment_methods' => $payment_methods
        ], 200);
    }

    public function addCustomerCreditView()
    {
        $customers = User::select(['id', 'first_name', 'last_name'])->where('type', '=', 2)->orderBy('first_name', 'asc')->get();

        return response([
            'customers' => $customers
        ], 200);
    }


}

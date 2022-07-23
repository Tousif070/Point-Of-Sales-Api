<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleTransaction;
use App\Models\PurchaseTransaction;
use App\Models\ExpenseTransaction;
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
            ->leftJoin('payments as p', function($query) {

                $query->on('p.transaction_id', '=', 'sale_transactions.id')
                    ->where('p.payment_for', '=', 'sale');

            })
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

    public function purchasePaymentView()
    {
        if(!auth()->user()->hasPermission("money-transaction.purchase-payment"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $purchase_transactions = PurchaseTransaction::join('users as u', 'u.id', '=', 'purchase_transactions.supplier_id')
            ->leftJoin('payments as p', function($query) {

                $query->on('p.transaction_id', '=', 'purchase_transactions.id')
                    ->where('p.payment_for', '=', 'purchase');

            })
            ->select(

                'purchase_transactions.id',
                DB::raw('DATE_FORMAT(purchase_transactions.transaction_date, "%m/%d/%Y") as date'),
                'purchase_transactions.reference_no',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as supplier'),
                'purchase_transactions.payment_status',
                DB::raw('purchase_transactions.amount as total_payable'),
                DB::raw('IFNULL(SUM(p.amount), 0) as paid'),
                DB::raw('
                
                    purchase_transactions.amount - IFNULL(SUM(p.amount), 0)
                
                as due')

            )->where('purchase_transactions.payment_status', '<>', 'Paid')
            ->groupBy('purchase_transactions.id')
            ->orderBy('purchase_transactions.transaction_date', 'desc')
            ->get();

        return response(['purchase_transactions' => $purchase_transactions], 200);
    }

    public function expensePaymentView()
    {
        if(!auth()->user()->hasPermission("money-transaction.expense-payment"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $expense_transactions = ExpenseTransaction::join('expense_categories as ec', 'ec.id', '=', 'expense_transactions.expense_category_id')
            ->join('expense_references as er', 'er.id', '=', 'expense_transactions.expense_reference_id')
            ->leftJoin('payments as p', function($query) {

                $query->on('p.transaction_id', '=', 'expense_transactions.id')
                    ->where('p.payment_for', '=', 'expense');

            })
            ->select(

                'expense_transactions.id',
                DB::raw('DATE_FORMAT(expense_transactions.transaction_date, "%m/%d/%Y") as date'),
                'expense_transactions.expense_no',
                'er.name as reference',
                'ec.name as category',
                'expense_transactions.payment_status',
                DB::raw('expense_transactions.amount as total_payable'),
                DB::raw('IFNULL(SUM(p.amount), 0) as paid'),
                DB::raw('
                
                    expense_transactions.amount - IFNULL(SUM(p.amount), 0)
                
                as due')

            )->where('expense_transactions.payment_status', '<>', 'Paid')
            ->groupBy('expense_transactions.id')
            ->orderBy('expense_transactions.transaction_date', 'desc')
            ->get();

        return response(['expense_transactions' => $expense_transactions], 200);
    }

    public function customerDropdown()
    {
        $customers = User::select(['id', 'first_name', 'last_name'])->where('type', '=', 2)->orderBy('first_name', 'asc')->get();

        return response([
            'customers' => $customers
        ], 200);
    }

    public function collectiveSalePaymentView(Request $request)
    {
        if(!auth()->user()->hasPermission("money-transaction.sale-payment"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        if(empty($request->customer_id))
        {
            return response(['sale_transactions' => []], 200);
        }

        $sale_transactions = SaleTransaction::join('users as u', 'u.id', '=', 'sale_transactions.customer_id')
            ->leftJoin('payments as p', function($query) {

                $query->on('p.transaction_id', '=', 'sale_transactions.id')
                    ->where('p.payment_for', '=', 'sale');

            })
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
            ->where('sale_transactions.customer_id', '=', $request->customer_id)
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

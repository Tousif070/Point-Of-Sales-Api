<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleTransaction;
use App\Models\SaleReturnTransaction;
use App\Models\PurchaseTransaction;
use App\Models\ExpenseTransaction;
use App\Models\Payment;
use App\Models\User;
use CAS;
use DB;

class ReportController extends Controller
{
    public function casIndexView()
    {
        if(!in_array("super_admin", auth()->user()->getRoles()) && auth()->user()->hasPermission("user.cua-enable"))
        {
            $customer_ids = auth()->user()->associatedCustomers()->pluck('customer_user_associations.customer_id');

            $customers = User::select(['id', 'first_name', 'last_name'])->where('type', '=', 2)->whereIn('id', $customer_ids)->orderBy('first_name', 'asc')->get();
        }
        else
        {
            $customers = User::select(['id', 'first_name', 'last_name'])->where('type', '=', 2)->orderBy('first_name', 'asc')->get();
        }

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
            ->orderBy('payments.payment_no', 'desc')
            ->get();

        return response(['payments' => $payments], 200);
    }

    public function pprIndex()
    {
        if(!auth()->user()->hasPermission("report.ppr-index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $payments = Payment::join('users as u', 'u.id', '=', 'payments.finalized_by')
            ->join('purchase_transactions as pt', 'pt.id', '=', 'payments.transaction_id')
            ->join('payment_methods as pm', 'pm.id', '=', 'payments.payment_method_id')
            ->select(

                'payments.id',
                DB::raw('DATE_FORMAT(payments.payment_date, "%m/%d/%Y") as date'),
                'payments.payment_no',
                'pt.reference_no as purchase_invoice',
                'payments.amount',
                'pm.name as payment_method',
                'payments.payment_note',
                DB::raw('CONCAT_WS(" ", u.first_name, DATE_FORMAT(payments.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by')

            )->where('payments.payment_for', '=', 'purchase')
            ->orderBy('payments.payment_date', 'desc')
            ->orderBy('payments.payment_no', 'desc')
            ->get();

        return response(['payments' => $payments], 200);
    }

    public function eprIndex()
    {
        if(!auth()->user()->hasPermission("report.epr-index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $payments = Payment::join('users as u', 'u.id', '=', 'payments.finalized_by')
            ->join('expense_transactions as et', 'et.id', '=', 'payments.transaction_id')
            ->join('payment_methods as pm', 'pm.id', '=', 'payments.payment_method_id')
            ->select(

                'payments.id',
                DB::raw('DATE_FORMAT(payments.payment_date, "%m/%d/%Y") as date'),
                'payments.payment_no',
                'et.expense_no',
                'payments.amount',
                'pm.name as payment_method',
                'payments.payment_note',
                DB::raw('CONCAT_WS(" ", u.first_name, DATE_FORMAT(payments.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by')

            )->where('payments.payment_for', '=', 'expense')
            ->orderBy('payments.payment_date', 'desc')
            ->orderBy('payments.payment_no', 'desc')
            ->get();

        return response(['payments' => $payments], 200);
    }

    public function profitBySaleInvoice()
    {
        if(!auth()->user()->hasPermission("report.pbsi"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $profit_by_sale_invoice = [];

        $serial = 0;

        $sale_transactions = SaleTransaction::join('sale_variations as sv', 'sv.sale_transaction_id', '=', 'sale_transactions.id')
            ->join('purchase_variations as pv', 'pv.id', '=', 'sv.purchase_variation_id')
            ->join('users as u', 'u.id', '=', 'sale_transactions.customer_id')
            ->select(

                'sale_transactions.invoice_no',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as customer'),
                DB::raw('SUM((sv.selling_price - sv.purchase_price) * sv.quantity) as gross_profit'),
                DB::raw('( SUM((sv.selling_price - sv.purchase_price) * sv.quantity) / SUM(sv.quantity) ) as avg_profit'),
                DB::raw('SUM( ((sv.selling_price - sv.purchase_price) * sv.quantity) * pv.risk_fund ) as risk_fund')

            )->where('sale_transactions.status', '=', 'Final')
            ->groupBy('sale_transactions.id')
            ->orderBy('sale_transactions.id', 'asc');
        
        $sale_return_transactions = SaleReturnTransaction::join('sale_return_variations as srv', 'srv.sale_return_transaction_id', '=', 'sale_return_transactions.id')
            ->join('sale_transactions as st', 'st.id', '=', 'sale_return_transactions.sale_transaction_id')
            ->join('purchase_variations as pv', 'pv.id', '=', 'srv.purchase_variation_id')
            ->join('users as u', 'u.id', '=', 'st.customer_id')
            ->select(

                'sale_return_transactions.invoice_no',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as customer'),
                DB::raw('SUM((srv.selling_price - srv.purchase_price) * srv.quantity) * (-1) as gross_profit'),
                DB::raw('( SUM((srv.selling_price - srv.purchase_price) * srv.quantity) / SUM(srv.quantity) ) * (-1) as avg_profit'),
                DB::raw('SUM( ((srv.selling_price - srv.purchase_price) * srv.quantity) * pv.risk_fund ) * (-1) as risk_fund')

            )->groupBy('sale_return_transactions.id')
            ->orderBy('sale_return_transactions.id', 'asc');
        
        foreach($sale_transactions->get() as $st)
        {
            $st->key = ++$serial;

            $st->type = "Sale";

            $profit_by_sale_invoice[] = $st;
        }

        foreach($sale_return_transactions->get() as $srt)
        {
            $srt->key = ++$serial;

            $srt->type = "Sale Return";

            $profit_by_sale_invoice[] = $srt;
        }

        return response(['profit_by_sale_invoice' => $profit_by_sale_invoice], 200);
    }

    public function profitByCustomerView()
    {
        $customers = User::select(['id', 'first_name', 'last_name'])->where('type', '=', 2)->orderBy('first_name', 'asc')->get();

        return response([
            'customers' => $customers
        ], 200);
    }

    public function profitByCustomer(Request $request)
    {
        if(!auth()->user()->hasPermission("report.pbc"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $profit_by_customer = [];

        $serial = 0;

        $sale_transactions = SaleTransaction::join('sale_variations as sv', 'sv.sale_transaction_id', '=', 'sale_transactions.id')
            ->join('users as u', 'u.id', '=', 'sale_transactions.customer_id')
            ->select(

                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as customer'),
                DB::raw('SUM((sv.selling_price - sv.purchase_price) * sv.quantity) as gross_profit')

            )->where('sale_transactions.status', '=', 'Final')
            ->groupBy('sale_transactions.customer_id')
            ->orderBy('u.first_name', 'asc');
        
        $sale_return_transactions = SaleReturnTransaction::join('sale_return_variations as srv', 'srv.sale_return_transaction_id', '=', 'sale_return_transactions.id')
            ->join('sale_transactions as st', 'st.id', '=', 'sale_return_transactions.sale_transaction_id')
            ->join('users as u', 'u.id', '=', 'st.customer_id')
            ->select(

                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as customer'),
                DB::raw('SUM((srv.selling_price - srv.purchase_price) * srv.quantity) * (-1) as gross_profit')

            )->groupBy('st.customer_id')
            ->orderBy('u.first_name', 'asc');
        
        
        if(!empty($request->customer_id))
        {
            $sale_transactions->where('sale_transactions.customer_id', '=', $request->customer_id);

            $sale_return_transactions->where('st.customer_id', '=', $request->customer_id);
        }
        
        
        foreach($sale_transactions->get() as $st)
        {
            $st->key = ++$serial;

            $st->type = "Sale";

            $profit_by_customer[] = $st;
        }

        foreach($sale_return_transactions->get() as $srt)
        {
            $srt->key = ++$serial;

            $srt->type = "Sale Return";

            $profit_by_customer[] = $srt;
        }

        return response(['profit_by_customer' => $profit_by_customer], 200);
    }

    public function profitByDate()
    {
        if(!auth()->user()->hasPermission("report.pbd"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $profit_by_date = SaleTransaction::join('sale_variations as sv', 'sv.sale_transaction_id', '=', 'sale_transactions.id')
            ->select(

                DB::raw('DATE_FORMAT(sale_transactions.transaction_date, "%m/%d/%Y") as date'),
                DB::raw('SUM((sv.selling_price - sv.purchase_price) * (sv.quantity - sv.return_quantity)) as gross_profit')

            )->where('sale_transactions.status', '=', 'Final')
            ->groupBy('sale_transactions.transaction_date')
            ->orderBy('sale_transactions.transaction_date', 'asc')
            ->get();

        return response(['profit_by_date' => $profit_by_date], 200);
    }

    public function profitByProducts()
    {
        if(!auth()->user()->hasPermission("report.pbp"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $profit_by_products = SaleTransaction::join('sale_variations as sv', 'sv.sale_transaction_id', '=', 'sale_transactions.id')
            ->join('products as p', 'p.id', '=', 'sv.product_id')
            ->select(

                'p.name',
                DB::raw('SUM((sv.selling_price - sv.purchase_price) * (sv.quantity - sv.return_quantity)) as gross_profit')

            )->where('sale_transactions.status', '=', 'Final')
            ->groupBy('p.id')
            ->orderBy('p.name', 'asc')
            ->get();

        return response(['profit_by_products' => $profit_by_products], 200);
    }

    public function profitByProductModels()
    {
        if(!auth()->user()->hasPermission("report.pbpm"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $profit_by_product_models = SaleTransaction::join('sale_variations as sv', 'sv.sale_transaction_id', '=', 'sale_transactions.id')
            ->join('products as p', 'p.id', '=', 'sv.product_id')
            ->join('product_models as pm', 'pm.id', '=', 'p.product_model_id')
            ->select(

                'pm.name',
                DB::raw('SUM((sv.selling_price - sv.purchase_price) * (sv.quantity - sv.return_quantity)) as gross_profit')

            )->where('sale_transactions.status', '=', 'Final')
            ->groupBy('pm.id')
            ->orderBy('pm.name', 'asc')
            ->get();

        return response(['profit_by_product_models' => $profit_by_product_models], 200);
    }

    public function profitByProductCategories()
    {
        if(!auth()->user()->hasPermission("report.pbpc"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $profit_by_product_categories = SaleTransaction::join('sale_variations as sv', 'sv.sale_transaction_id', '=', 'sale_transactions.id')
            ->join('products as p', 'p.id', '=', 'sv.product_id')
            ->join('product_categories as pc', 'pc.id', '=', 'p.product_category_id')
            ->select(

                'pc.name',
                DB::raw('SUM((sv.selling_price - sv.purchase_price) * (sv.quantity - sv.return_quantity)) as gross_profit')

            )->where('sale_transactions.status', '=', 'Final')
            ->groupBy('pc.id')
            ->orderBy('pc.name', 'asc')
            ->get();

        return response(['profit_by_product_categories' => $profit_by_product_categories], 200);
    }

    public function verificationReport()
    {
        if(!auth()->user()->hasPermission("report.verification"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $verification_report =  [];

        $sale_verification = SaleTransaction::select(

            DB::raw('"Sale" as type'),
            DB::raw('IFNULL( SUM( IF( verification_status = 2, 1, 0 ) ), 0 ) as not_verified'),
            DB::raw('IFNULL( SUM( IF( verification_status = 1, 1, 0 ) ), 0 ) as verified_okay'),
            DB::raw('IFNULL( SUM( IF( verification_status = 0, 1, 0 ) ), 0 ) as verified_not_okay')

        )->first();
        

        $sale_return_verification = SaleReturnTransaction::select(

            DB::raw('"Sale Return" as type'),
            DB::raw('IFNULL( SUM( IF( verification_status = 2, 1, 0 ) ), 0 ) as not_verified'),
            DB::raw('IFNULL( SUM( IF( verification_status = 1, 1, 0 ) ), 0 ) as verified_okay'),
            DB::raw('IFNULL( SUM( IF( verification_status = 0, 1, 0 ) ), 0 ) as verified_not_okay')

        )->first();
        

        $purchase_verification = PurchaseTransaction::select(

            DB::raw('"Purchase" as type'),
            DB::raw('IFNULL( SUM( IF( verification_status = 2, 1, 0 ) ), 0 ) as not_verified'),
            DB::raw('IFNULL( SUM( IF( verification_status = 1, 1, 0 ) ), 0 ) as verified_okay'),
            DB::raw('IFNULL( SUM( IF( verification_status = 0, 1, 0 ) ), 0 ) as verified_not_okay')

        )->first();
        

        $expense_verification = ExpenseTransaction::select(

            DB::raw('"Expense" as type'),
            DB::raw('IFNULL( SUM( IF( verification_status = 2, 1, 0 ) ), 0 ) as not_verified'),
            DB::raw('IFNULL( SUM( IF( verification_status = 1, 1, 0 ) ), 0 ) as verified_okay'),
            DB::raw('IFNULL( SUM( IF( verification_status = 0, 1, 0 ) ), 0 ) as verified_not_okay')

        )->first();
        

        $payment_verification = Payment::select(

            DB::raw('"Payment" as type'),
            DB::raw('IFNULL( SUM( IF( verification_status = 2, 1, 0 ) ), 0 ) as not_verified'),
            DB::raw('IFNULL( SUM( IF( verification_status = 1, 1, 0 ) ), 0 ) as verified_okay'),
            DB::raw('IFNULL( SUM( IF( verification_status = 0, 1, 0 ) ), 0 ) as verified_not_okay')

        )->first();

        
        $verification_report[] = $sale_verification;

        $verification_report[] = $sale_return_verification;

        $verification_report[] = $purchase_verification;

        $verification_report[] = $expense_verification;

        $verification_report[] = $payment_verification;


        return response(['verification_report' => $verification_report], 200);
    }


}

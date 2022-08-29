<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SaleTransaction;
use App\Models\SaleReturnTransaction;
use App\Models\User;
use DB;

class SalesmanReportController extends Controller
{
    //  FIRST PERSON VIEW
    public function commissionBySaleInvoiceFp(Request $request)
    {
        if(!auth()->user()->hasPermission("srfp"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $commission_by_sale_invoice = SaleTransaction::join('sale_variations as sv', 'sv.sale_transaction_id', '=', 'sale_transactions.id')
            ->join('users as u', 'u.id', '=', 'sale_transactions.customer_id')
            ->select(

                'sale_transactions.id',
                DB::raw('DATE_FORMAT(sale_transactions.transaction_date, "%m/%d/%Y") as date'),
                'sale_transactions.invoice_no',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as customer'),
                'sale_transactions.payment_status',
                DB::raw('SUM((sv.selling_price - sv.purchase_price) * sv.quantity) * 0.2 as commission')

            )->where('sale_transactions.status', '=', 'Final')            
            ->groupBy('sale_transactions.id')
            ->orderBy('sale_transactions.transaction_date', 'desc')
            ->orderBy('sale_transactions.invoice_no', 'desc');

            
        if(!in_array("super_admin", auth()->user()->getRoles()) && auth()->user()->hasPermission("user.cua-enable"))
        {
            $customer_ids = auth()->user()->associatedCustomers()->pluck('customer_user_associations.customer_id');

            $commission_by_sale_invoice->whereIn('sale_transactions.customer_id', $customer_ids);
        } 
        
        if(!empty($request->payment_status))
        {
            $commission_by_sale_invoice->where('sale_transactions.payment_status', '=', $request->payment_status);
        }

        return response(['commission_by_sale_invoice' => $commission_by_sale_invoice->get()], 200);
    }


    public function commissionByCustomerFp()
    {
        if(!auth()->user()->hasPermission("srfp"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $commission_by_customer = SaleTransaction::join('sale_variations as sv', 'sv.sale_transaction_id', '=', 'sale_transactions.id')
            ->join('users as u', 'u.id', '=', 'sale_transactions.customer_id')
            ->select(
                
                'sale_transactions.customer_id',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as customer'),
                DB::raw('SUM((sv.selling_price - sv.purchase_price) * (sv.quantity - sv.return_quantity)) * 0.2 as commission')

            )->where('sale_transactions.status', '=', 'Final')
            ->groupBy('sale_transactions.customer_id')
            ->orderBy('sale_transactions.customer_id', 'asc');

            
            if(!in_array("super_admin", auth()->user()->getRoles()) && auth()->user()->hasPermission("user.cua-enable"))
            {
                $customer_ids = auth()->user()->associatedCustomers()->pluck('customer_user_associations.customer_id');
    
                $commission_by_customer->whereIn('sale_transactions.customer_id', $customer_ids);
            }

        return response(['commission_by_customer' => $commission_by_customer->get()], 200);
        
    }

    public function salesDueFp()
    {
        if(!auth()->user()->hasPermission("srfp"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $sales_due = SaleTransaction::join('users as u', 'u.id', '=', 'sale_transactions.customer_id')
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

        )->where('sale_transactions.status', '=', 'Final')  
        ->where('sale_transactions.payment_status', '<>', 'Paid')
        ->groupBy('sale_transactions.id')
        ->orderBy('sale_transactions.transaction_date', 'desc');

            
        if(!in_array("super_admin", auth()->user()->getRoles()) && auth()->user()->hasPermission("user.cua-enable"))
        {
            $customer_ids = auth()->user()->associatedCustomers()->pluck('customer_user_associations.customer_id');

            $sales_due->whereIn('sale_transactions.customer_id', $customer_ids);
        }

        return response(['sales_due' => $sales_due->get()], 200);
    }

    public function commissionByReturnInvoiceFp()
    {
        if(!auth()->user()->hasPermission("srfp"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $commission_by_return_invoice = SaleReturnTransaction::join('sale_return_variations as srv', 'srv.sale_return_transaction_id', '=', 'sale_return_transactions.id')
        ->join('sale_transactions as st', 'st.id', '=', 'sale_return_transactions.sale_transaction_id')
        ->join('users as u', 'u.id', '=', 'st.customer_id')
        ->select(

            'sale_return_transactions.id',
            DB::raw('DATE_FORMAT(sale_return_transactions.transaction_date, "%m/%d/%Y") as date'),
            'sale_return_transactions.invoice_no as return_invoice',
            'st.invoice_no as sale_invoice',
            DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as customer'),
            DB::raw('SUM((srv.selling_price - srv.return_deduction - srv.purchase_price) * srv.quantity) * 0.2 as commission')

        )->where('st.status', '=', 'Final')
        ->groupBy('sale_return_transactions.id')
        ->orderBy('sale_return_transactions.id', 'asc')
        ->orderBy('sale_return_transactions.invoice_no', 'desc');

            
        if(!in_array("super_admin", auth()->user()->getRoles()) && auth()->user()->hasPermission("user.cua-enable"))
        {
            $customer_ids = auth()->user()->associatedCustomers()->pluck('customer_user_associations.customer_id');

            $commission_by_return_invoice->whereIn('st.customer_id', $customer_ids);
        }

        return response(['commission_by_return_invoice' => $commission_by_return_invoice->get()], 200);

    }


    // THIRD PERSON VIEW
    public function salesmanDropdown()
    {
        $salesmen = User::join('customer_user_associations as cua', 'cua.user_official_id', '=', 'users.id')
        ->select(

            'users.id',
            'users.first_name',
            'users.last_name'

        )->where('users.type', '=', 1)
        ->distinct('users.id')
        ->orderBy('users.first_name', 'asc')
        ->get();

        return response(['salesmen' => $salesmen], 200);
    }

    public function commissionBySaleInvoiceTp(Request $request)
    {
        if(!auth()->user()->hasPermission("srtp"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        if(empty($request->salesman_id))
        {
            return response(['commission_by_sale_invoice' => []], 200);
        }

        $commission_by_sale_invoice = SaleTransaction::join('sale_variations as sv', 'sv.sale_transaction_id', '=', 'sale_transactions.id')
            ->join('users as u', 'u.id', '=', 'sale_transactions.customer_id')
            ->select(

                'sale_transactions.id',
                DB::raw('DATE_FORMAT(sale_transactions.transaction_date, "%m/%d/%Y") as date'),
                'sale_transactions.invoice_no',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as customer'),
                'sale_transactions.payment_status',
                DB::raw('SUM((sv.selling_price - sv.purchase_price) * sv.quantity) * 0.2 as commission')

            )->where('sale_transactions.status', '=', 'Final')            
            ->groupBy('sale_transactions.id')
            ->orderBy('sale_transactions.transaction_date', 'desc')
            ->orderBy('sale_transactions.invoice_no', 'desc');


        $salesman = User::where('id', '=', $request->salesman_id)->where('type', '=', 1)->first();

        if($salesman == null)
        {
            return response(['message' => 'Salesman not found !'], 404);
        }

            
        if($salesman->hasPermission("user.cua-enable"))
        {
            $customer_ids = $salesman->associatedCustomers()->pluck('customer_user_associations.customer_id');

            $commission_by_sale_invoice->whereIn('sale_transactions.customer_id', $customer_ids);
        }

        
        if(!empty($request->payment_status))
        {
            $commission_by_sale_invoice->where('sale_transactions.payment_status', '=', $request->payment_status);
        }

        return response(['commission_by_sale_invoice' => $commission_by_sale_invoice->get()], 200);

    }


    public function commissionByCustomerTp(Request $request)
    {
        if(!auth()->user()->hasPermission("srtp"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        if(empty($request->salesman_id))
        {
            return response(['commission_by_sale_invoice' => []], 200);
        }

        $commission_by_customer = SaleTransaction::join('sale_variations as sv', 'sv.sale_transaction_id', '=', 'sale_transactions.id')
            ->join('users as u', 'u.id', '=', 'sale_transactions.customer_id')
            ->select(
                
                'sale_transactions.customer_id',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as customer'),
                DB::raw('SUM((sv.selling_price - sv.purchase_price) * (sv.quantity - sv.return_quantity)) * 0.2 as commission')

            )->where('sale_transactions.status', '=', 'Final')
            ->groupBy('sale_transactions.customer_id')
            ->orderBy('sale_transactions.customer_id', 'asc');

            
        $salesman = User::where('id', '=', $request->salesman_id)->where('type', '=', 1)->first();

        if($salesman == null)
        {
            return response(['message' => 'Salesman not found !'], 404);
        }

            
        if($salesman->hasPermission("user.cua-enable"))
        {
            $customer_ids = $salesman->associatedCustomers()->pluck('customer_user_associations.customer_id');
    
            $commission_by_customer->whereIn('sale_transactions.customer_id', $customer_ids);
        }

        return response(['commission_by_customer' => $commission_by_customer->get()], 200);
        
    }

    public function salesDueTp(Request $request)
    {
        if(!auth()->user()->hasPermission("srtp"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        if(empty($request->salesman_id))
        {
            return response(['commission_by_sale_invoice' => []], 200);
        }

        $sales_due = SaleTransaction::join('users as u', 'u.id', '=', 'sale_transactions.customer_id')
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

        )->where('sale_transactions.status', '=', 'Final')  
        ->where('sale_transactions.payment_status', '<>', 'Paid')
        ->groupBy('sale_transactions.id')
        ->orderBy('sale_transactions.transaction_date', 'desc');

        $salesman = User::where('id', '=', $request->salesman_id)->where('type', '=', 1)->first();

        if($salesman == null)
        {
            return response(['message' => 'Salesman not found !'], 404);
        }

            
        if($salesman->hasPermission("user.cua-enable"))
        {
            $customer_ids = $salesman->associatedCustomers()->pluck('customer_user_associations.customer_id');

            $sales_due->whereIn('sale_transactions.customer_id', $customer_ids);
        }

        return response(['sales_due' => $sales_due->get()], 200);
    }

    public function commissionByReturnInvoiceTp(Request $request)
    {        
        if(!auth()->user()->hasPermission("srtp"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        if(empty($request->salesman_id))
        {
            return response(['commission_by_return_invoice' => []], 200);
        }

        

        $commission_by_return_invoice = SaleReturnTransaction::join('sale_return_variations as srv', 'srv.sale_return_transaction_id', '=', 'sale_return_transactions.id')
        ->join('sale_transactions as st', 'st.id', '=', 'sale_return_transactions.sale_transaction_id')
        ->join('users as u', 'u.id', '=', 'st.customer_id')
        ->select(

            'sale_return_transactions.id',
            DB::raw('DATE_FORMAT(sale_return_transactions.transaction_date, "%m/%d/%Y") as date'),
            'sale_return_transactions.invoice_no as return_invoice',
            'st.invoice_no as sale_invoice',
            DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as customer'),
            DB::raw('SUM((srv.selling_price - srv.return_deduction - srv.purchase_price) * srv.quantity) * 0.2 as commission')

        )->where('st.status', '=', 'Final')
        ->groupBy('sale_return_transactions.id')
        ->orderBy('sale_return_transactions.id', 'asc')
        ->orderBy('sale_return_transactions.invoice_no', 'desc');
        
        $salesman = User::where('id', '=', $request->salesman_id)->where('type', '=', 1)->first();

        if($salesman == null)
        {
            return response(['message' => 'Salesman not found !'], 404);
        }

            
        if($salesman->hasPermission("user.cua-enable"))
        {
            $customer_ids = $salesman->associatedCustomers()->pluck('customer_user_associations.customer_id');

            $commission_by_return_invoice->whereIn('st.customer_id', $customer_ids);
        }

        return response(['commission_by_return_invoice' => $commission_by_return_invoice->get()], 200);

    }

    public function commissionSummaryTp()
    {   
        if(!auth()->user()->hasPermission("srtp"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $commission_summary = [];

        $commissions = SaleTransaction::join('sale_variations as sv', 'sv.sale_transaction_id', '=', 'sale_transactions.id')
        ->join('customer_user_associations as cua', 'cua.customer_id', '=', 'sale_transactions.customer_id' )
        ->join('users as u', 'u.id', '=', 'cua.user_official_id' )
            ->select(

                'u.id',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as salesman'),
                DB::raw('SUM((sv.selling_price - sv.purchase_price) * sv.quantity) * 0.2 as commission')

            )->where('sale_transactions.status', '=', 'Final')            
            ->groupBy('cua.user_official_id')
            ->orderBy('u.first_name', 'asc')
            ->get();


        $commission_returns = SaleReturnTransaction::join('sale_return_variations as srv', 'srv.sale_return_transaction_id', '=', 'sale_return_transactions.id')
        ->join('sale_transactions as st', 'st.id', '=', 'sale_return_transactions.sale_transaction_id')
        ->join('customer_user_associations as cua', 'cua.customer_id', '=', 'st.customer_id' )
        ->join('users as u', 'u.id', '=', 'cua.user_official_id' )
            ->select(

                'u.id',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as salesman'),
                DB::raw('SUM((srv.selling_price - srv.return_deduction - srv.purchase_price) * srv.quantity) * 0.2 as commission_return')

            )->where('st.status', '=', 'Final')            
            ->groupBy('cua.user_official_id')
            ->orderBy('u.first_name', 'asc')
            ->get();
            
        
        foreach($commissions as $commission)
        {
            // $commission_summary[$commission->salesman]['commission'] = $commission->commission;
            
            $commission_summary[$commission->id]['salesman'] = $commission->salesman;

            $commission_summary[$commission->id]['commission'] = $commission->commission;

            $commission_summary[$commission->id]['commission_return'] = 0;
        }

        foreach($commission_returns as $commission_return)
        {
            // $commission_summary[$commission_return->salesman]['commission_return'] = $commission_return->commission_return;
            
            // $commission_summary[$commission_return->salesman]['salesman'] = $commission_return->salesman;

            $commission_summary[$commission_return->id]['commission_return'] = $commission_return->commission_return;
        }

        return response(['commission_summary' => $commission_summary], 200);        
        
    }

    public function salesDueSummaryTp()
    {   
        if(!auth()->user()->hasPermission("srtp"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $sales_due_summary = SaleTransaction::join('customer_user_associations as cua', 'cua.customer_id', '=', 'sale_transactions.customer_id' )
        ->join('users as u', 'u.id', '=', 'cua.user_official_id' )
        ->select(

            'u.id',
            DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as salesman'),
            DB::raw('
            
            SUM(sale_transactions.amount) - 
            SUM(IFNULL((select SUM(amount) from sale_return_transactions where sale_transaction_id = sale_transactions.id), 0)) - 
            SUM(IFNULL((select SUM(amount) from payments where transaction_id = sale_transactions.id and `payment_for` = "sale"), 0)) as due')

        )->where('sale_transactions.status', '=', 'Final')
        ->where('sale_transactions.payment_status', '<>', 'Paid')
        ->groupBy('cua.user_official_id')
        ->orderBy('u.first_name', 'asc')
        ->get();
        
        return response(['sales_due_summary' => $sales_due_summary], 200);        
        
    }

}

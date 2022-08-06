<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExpenseCategory;
use App\Models\ExpenseTransaction;
use App\Models\User;
use REC;
use DB;
use Exception;
use Carbon\Carbon;

class ExpenseTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->hasPermission("expense.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $expense_transactions = ExpenseTransaction::join('expense_categories as ec', 'ec.id', '=', 'expense_transactions.expense_category_id')
            ->join('users as u', 'u.id', '=', 'expense_transactions.finalized_by')
            ->leftJoin('users as u2', 'u2.id', '=', 'expense_transactions.expense_for')
            ->leftJoin('users as u3', 'u3.id', '=', 'expense_transactions.verified_by')
            ->select(

                'expense_transactions.id',
                'expense_transactions.verification_status',
                'expense_transactions.verification_note',
                DB::raw('DATE_FORMAT(expense_transactions.transaction_date, "%m/%d/%Y") as date'),
                'expense_transactions.expense_no',
                'ec.name as category',
                'expense_transactions.amount',
                'expense_transactions.payment_status',
                DB::raw('CONCAT_WS(" ", u2.first_name, u2.last_name) as expense_for'),
                'expense_transactions.expense_note',
                DB::raw('CONCAT_WS(" ", u.first_name, DATE_FORMAT(expense_transactions.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by'),
                DB::raw('CONCAT_WS(" ", u3.first_name, DATE_FORMAT(expense_transactions.verified_at, "%m/%d/%Y %H:%i:%s")) as verified_by')

            )->orderBy('expense_transactions.transaction_date', 'desc')
            ->get();

        return response(['expense_transactions' => $expense_transactions], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->hasPermission("expense.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'expense_category_id' => 'required | numeric',
            'amount' => 'required | numeric',
            'transaction_date' => 'required | date',
            'expense_for' => 'nullable | numeric',
            'expense_note' => 'required | string'
        ], [
            'expense_category_id.required' => 'Please select the expense category !',
            'expense_category_id.numeric' => 'Expense Category ID should be numeric !',

            'amount.required' => 'Please enter the amount of expense !',
            'amount.numeric' => 'Expense amount should be numeric !',

            'transaction_date.required' => 'Please specify the transaction date !',
            'transaction_date.date' => 'Please specify a valid date !',

            'expense_for.numeric' => 'Expense for should be numeric !',

            'expense_note.required' => 'Please enter a note for this expense !',
            'expense_note.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !'
        ]);

        DB::beginTransaction();

        try {

            $expense_transaction = new ExpenseTransaction();

            $expense_transaction->expense_category_id = $request->expense_category_id;

            $expense_transaction->amount = $request->amount;

            $expense_transaction->payment_status = "Due";

            $expense_transaction->transaction_date = Carbon::parse($request->transaction_date);

            $expense_transaction->expense_for = $request->expense_for;

            $expense_transaction->expense_note = $request->expense_note;

            $expense_transaction->finalized_by = auth()->user()->id;

            $expense_transaction->finalized_at = Carbon::now();

            $expense_transaction->save();


            $expense_transaction->expense_no = "Exp#" . ($expense_transaction->id + 1000);

            $expense_transaction->save();


            // RECORD ENTRY FOR EXPENSE TRANSACTION
            $rec_data_arr = [
                'category' => 'Transaction',
                'type' => 'Expense',
                'reference_id' => $expense_transaction->id,
                'cash_flow' => null,
                'amount' => $expense_transaction->amount
            ];

            REC::store($rec_data_arr);


            DB::commit();

            return response(['expense_transaction' => $expense_transaction], 201);

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

    public function storeExpenseView()
    {
        $expense_categories = ExpenseCategory::select(['id', 'name'])->orderBy('name', 'asc')->get();

        $expense_for = User::select(['id', 'first_name', 'last_name'])->where('type', '=', 1)->orderBy('first_name', 'asc')->get();

        return response([
            'expense_categories' => $expense_categories,
            'expense_for' => $expense_for
        ], 200);
    }

    public function summary()
    {
        if(!auth()->user()->hasPermission("expense.summary"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $expense_summary = ExpenseTransaction::rightJoin('expense_categories as ec', 'ec.id', '=', 'expense_transactions.expense_category_id')
            ->select(

                'ec.id',
                'ec.name as category',
                DB::raw('IFNULL(SUM(expense_transactions.amount), 0) as total_amount'),

            )->groupBy('ec.id')
            ->orderBy('ec.name', 'asc')
            ->get();

        return response(['expense_summary' => $expense_summary], 200);
    }

    public function verification(Request $request)
    {
        if(!auth()->user()->hasPermission("expense.verification"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'expense_transaction_id' => 'required | numeric',
            'verification_status' => 'required | numeric',
            'verification_note' => 'string | nullable',
            'pin_number' => 'required | numeric'
        ], [
            'expense_transaction_id.required' => 'Expense Transaction ID is required',
            'expense_transaction_id.numeric' => 'Expense Transaction ID should be numeric',

            'verification_status.required' => 'Please specify the verification status !',
            'verification_status.numeric' => 'Verification Status should be numeric !',

            'verification_note.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',

            'pin_number.required' => 'Please enter your PIN Number !',
            'pin_number.numeric' => 'PIN Number should be numeric !'
        ]);

        
        if(auth()->user()->pin_number != $request->pin_number)
        {
            return response([
                'errors' => [
                    'pin_number' => ['Invalid Pin Number !']
                ]
            ], 409);
        }


        DB::beginTransaction();

        try {

            $expense_transaction = ExpenseTransaction::find($request->expense_transaction_id);

            $expense_transaction->verification_status = $request->verification_status;

            $expense_transaction->verification_note = $request->verification_note;

            $expense_transaction->verified_by = auth()->user()->id;

            $expense_transaction->verified_at = Carbon::now();

            $expense_transaction->save();

            DB::commit();

            return response([
                'expense_transaction_id' => $expense_transaction->id,
                'verification_status' => $request->verification_status,
                'verification_note' => $request->verification_note,
                'verified_by' => auth()->user()->first_name . " " . auth()->user()->last_name . " " . date_format(date_create($expense_transaction->verified_at), "m/d/Y H:i:s") 
            ], 200);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }


}

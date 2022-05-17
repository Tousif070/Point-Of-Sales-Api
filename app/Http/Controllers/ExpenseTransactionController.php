<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExpenseTransaction;
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

        $expense_transactions = ExpenseTransaction::all();

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
            'expense_reference_id' => 'required | numeric',
            'expense_category_id' => 'required | numeric',
            'amount' => 'required | numeric',
            'transaction_date' => 'required | date',
            'expense_for' => 'nullable | numeric',
            'expense_note' => 'required | string'
        ], [
            'expense_reference_id.required' => 'Please select the expense reference !',
            'expense_reference_id.numeric' => 'Expense Reference ID should be numeric !',

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

            $expense_transaction->expense_reference_id = $request->expense_reference_id;

            $expense_transaction->expense_category_id = $request->expense_category_id;

            $expense_transaction->amount = $request->amount;

            $expense_transaction->payment_status = "Paid";

            $expense_transaction->transaction_date = Carbon::parse($request->transaction_date);

            $expense_transaction->expense_for = $request->expense_for;

            $expense_transaction->expense_note = $request->expense_note;

            $expense_transaction->finalized_by = auth()->user()->id;

            $expense_transaction->finalized_at = Carbon::now();

            $expense_transaction->save();

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
}

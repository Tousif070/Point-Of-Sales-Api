<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExpenseCategory;
use DB;
use Exception;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->hasPermission("expense-category.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $expense_categories = ExpenseCategory::orderBy('name', 'asc')->get();

        return response(['expense_categories' => $expense_categories], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->hasPermission("expense-category.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'name' => 'required | string | unique:expense_categories,name'
        ], [
            'name.required' => 'Please enter the expense category name !',
            'name.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',
            'name.unique' => 'Expense category name already exists !'
        ]);

        DB::beginTransaction();

        try {

            $expense_category = new ExpenseCategory();

            $expense_category->name = $request->name;

            $expense_category->save();

            DB::commit();

            return response(['expense_category' => $expense_category], 201);

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

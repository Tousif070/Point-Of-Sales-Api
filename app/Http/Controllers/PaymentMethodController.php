<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use DB;
use Exception;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!auth()->user()->hasPermission("payment-method.index"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $payment_methods = PaymentMethod::orderBy('name', 'asc')->get();

        return response(['payment_methods' => $payment_methods], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!auth()->user()->hasPermission("payment-method.store"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'name' => 'required | string | unique:payment_methods,name'
        ], [
            'name.required' => 'Please enter the name of the payment method !',
            'name.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !',
            'name.unique' => 'Payment method already exists !'
        ]);

        DB::beginTransaction();

        try {

            $payment_method = new PaymentMethod();

            $payment_method->name = $request->name;

            $payment_method->save();

            DB::commit();

            return response(['payment_method' => $payment_method], 201);

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

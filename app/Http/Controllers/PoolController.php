<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pool;
use DB;
use Exception;
use Carbon\Carbon;

class PoolController extends Controller
{
    public function history()
    { 
        if(!auth()->user()->hasPermission("pool.history"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }           
        
        // LOOKING FOR THIS MONTH'S OPENING BALANCE
        
        $date = Carbon::today();
        
        $start = $date->year . "-" . $date->month . "-01";
        
        $opening_balance = Pool::where('type', '=', 'Opening Balance')
            ->whereDate('created_at', '=', $start)
            ->get();
        
        
        if(count($opening_balance) < 1)
        {
            // IF NOT FOUND THEN AT FIRST, CALCULATE AND STORE THE CLOSING BALANCE OF THE PREVIOUS MONTH
            // THEN ASSIGN THE CLOSING BALANCE OF THE PREVIOUS MONTH TO THE OPENING BALANCE OF THE CURRENT MONTH
            
            $date = Carbon::today()->subMonth();
                
            $start = $date->year . "-" . $date->month . "-01";
            
            $end = $date->year . "-" . $date->month . "-" . $date->daysInMonth . " 23:59:58";
            
            $overall_balance = Pool::whereDate('created_at', '>=', $start)
                ->whereDate('created_at', '<=', $end)
                ->sum('amount');
                
            $closing_balance = new Pool();
            
            $closing_balance->type = "Closing Balance";
            $closing_balance->amount = $overall_balance;
            $closing_balance->note = "N/A";
            
            $closing_balance->save();
            
            $closing_balance->created_at = $end;
            
            $closing_balance->save();
            
            
            $opening_balance = new Pool();
            
            $opening_balance->type = "Opening Balance";
            $opening_balance->amount = $overall_balance;
            $opening_balance->note = "N/A";
            
            $opening_balance->save();
            
            $date = Carbon::today();
                
            $opening_balance->created_at = $date->year . "-" . $date->month . "-01";
            
            $opening_balance->save();
        }
        


        
        $pools = Pool::leftJoin('users as u', 'u.id', '=', 'pools.finalized_by')
        ->select(

                'pools.id', 
                DB::raw('DATE_FORMAT(pools.created_at, "%m/%d/%Y") as date'),
                'pools.type', 
                'pools.note', 
                'pools.amount', 
                DB::raw('CONCAT_WS(" ", u.first_name, DATE_FORMAT(pools.finalized_at, "%m/%d/%Y %H:%i:%s")) as finalized_by')

            )
        ->orderBy('pools.created_at', 'asc')
        ->get();

        $latest_balance = 0;

        foreach($pools as $pool)
        {
            if($pool->type == "Add Money" || $pool->type == "Withdraw")
            {
                $latest_balance += $pool->amount;  
            }
                          
            $pool->balance = $latest_balance;
        }

        return response(["pools" => $pools], 200);
    }

    public function add(Request $request)
    {
        if(!auth()->user()->hasPermission("pool.add"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'amount' => 'required | numeric | min:1',
            'note' => 'string | nullable'
        ], [
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount should be numeric',
            'amount.min' => 'Amount cannot be less than 1 !',

            'note.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !'
        ]);

        

        DB::beginTransaction();

        try {

            $pool = new Pool();
            
            $pool->type = "Add Money";
            $pool->amount = $request->amount;
            $pool->note = $request->note;
            $pool->finalized_by = auth()->user()->id;
            $pool->finalized_at = Carbon::now();
            
            $pool->save();

            DB::commit();

            return response(['message' => 'Amount Added Successfully !'], 200);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }


        
    }

    public function withdraw(Request $request)
    {
        if(!auth()->user()->hasPermission("pool.withdraw"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }
        
        $request->validate([
            'amount' => 'required | numeric | min:1',
            'note' => 'string | nullable'
        ], [
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount should be numeric',
            'amount.min' => 'Amount cannot be less than 1 !',

            'note.string' => 'Only alphabets, numbers & special characters are allowed. Must be a string !'
        ]);

        $date = Carbon::today();
        
        $start = $date->year . "-" . $date->month . "-01";
        
        $end = $date->year . "-" . $date->month . "-" . $date->daysInMonth . " 23:58:58";
        
        $withdraw_limit = Pool::where('type', '!=', 'Closing Balance')
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->sum('amount');


        if($request->amount > $withdraw_limit)
        {
            return response([
                'errors' => [
                    'amount' => ['Withdraw limit exceeded !']
                ]
            ], 409);
        }

        
        DB::beginTransaction();

        try {

            $pool = new Pool();
        
            $pool->type = "Withdraw";
            $pool->amount = -$request->amount;
            $pool->note = $request->note;
            $pool->finalized_by = auth()->user()->id;
            $pool->finalized_at = Carbon::now();
            
            $pool->save();
            
            DB::commit();

            return response(['message' => 'Amount Withdrawn Successfully !'], 200);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }

    }

    public function update(Request $request, $id)
    {
        $pool = Pool::find($id);

        if($pool->type == "Add Money")
        {
            $pool->amount = $request->amount;
        }
        else if($pool->type == "Withdraw")
        {
            $pool->amount = -$request->amount;
        }

        $pool->note = $request->note;
        $pool->finalized_by = auth()->user()->id;
        $pool->finalized_at = Carbon::now();

        $pool->save();


        $pools = Pool::all();


        // $pools = Pool::where('id', '>', $id)
        // ->select('type', 'amount', 'note', 'finalized_by', 'created_at')        
        // ->orderBy('created_at', 'asc')
        // ->get();
        

        $latest_balance = 0;

        foreach($pools as $pool)
        {
            if($pool->type == "Add Money" || $pool->type == "Withdraw")
            {
                $latest_balance += $pool->amount;  
            }
            else 
            {
                $pool->amount = $latest_balance;
                $pool->save();
            }          
            $pool->balance = $latest_balance;
        }





        // foreach($pools as $pool)
        // {
        //     if($pool->type == "Add Money")
        //     {
        //         $latest_balance += $pool->amount;
                
        //         $pool->balance = $latest_balance;
        //     }

        //     else if($pool->type == "Withdraw")
        //     {
        //         $latest_balance += $pool->amount;
                
        //         $pool->balance = $latest_balance;
        //     }

        //     else if($pool->type == "Opening Balance")
        //     {                
        //         $pool->balance = $latest_balance;
        //     }

        //     else if($pool->type == "Closing Balance")
        //     {
        //         $pool->balance = $latest_balance;
        //     }
        // }



        return response(['pool' => $pool], 200);
    }
}

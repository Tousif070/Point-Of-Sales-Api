<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pool;
use DB;
use Exception;
use Carbon\Carbon;

class PoolController extends Controller
{
    public function checkOpeningBalance($date)
    {
        $start = $date->year . "-" . $date->month . "-01";
        
        $opening_balance = Pool::where('type', '=', 'Opening Balance')
            ->whereDate('created_at', '=', $start)
            ->get();
        
        if(count($opening_balance) > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function setOpeningAndClosingBalance($date)
    {
        $start = $date->year . "-" . $date->month . "-01";
        
        $end = $date->year . "-" . $date->month . "-" . $date->daysInMonth . " 23:59:58";

        $overall_balance = 0;

        if(!$this->checkOpeningBalance($date))
        {
            $overall_balance = $this->setOpeningAndClosingBalance($date->copy()->subMonth());
        }
        else
        {
            $overall_balance = Pool::whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->sum('amount');
        }

        // CLOSING BALANCE FOR CURRENT MONTH
        $closing_balance = new Pool();
            
        $closing_balance->type = "Closing Balance";
        $closing_balance->amount = $overall_balance;
        $closing_balance->note = "N/A";
        
        $closing_balance->save();
        
        $closing_balance->created_at = $end;
        
        $closing_balance->save();
        
        
        // OPENING BALANCE FOR NEXT MONTH
        $opening_balance = new Pool();
        
        $opening_balance->type = "Opening Balance";
        $opening_balance->amount = $overall_balance;
        $opening_balance->note = "N/A";
        
        $opening_balance->save();
        
        $date = $date->addMonth();
            
        $opening_balance->created_at = $date->year . "-" . $date->month . "-01";
        
        $opening_balance->save();

        return $overall_balance;
    }

    public function history()
    {
        if(!auth()->user()->hasPermission("pool.history"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $date = Carbon::today();

        if(!$this->checkOpeningBalance($date))
        {
            $this->setOpeningAndClosingBalance($date->copy()->subMonth());
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


        $date = Carbon::today();

        if(!$this->checkOpeningBalance($date))
        {
            $this->setOpeningAndClosingBalance($date->copy()->subMonth());
        }


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

        if(!$this->checkOpeningBalance($date))
        {
            $this->setOpeningAndClosingBalance($date->copy()->subMonth());
        }


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
            $pool->amount = $request->amount * (-1);
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
    
    public function update(Request $request, $pool_id)
    {
        if(!auth()->user()->hasPermission("pool.update"))
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

            $pool = Pool::find($pool_id);

            if($pool->type == "Opening Balance" || $pool->type == "Closing Balance")
            {
                DB::rollBack();

                return response([
                    'errors' => [
                        'message' => ['Cannot modify Opening/Closing balance !']
                    ]
                ], 409);
            }
    
            $diff = 0;
    
            if($pool->type == "Add Money")
            {
                $diff = $pool->amount - $request->amount;
    
                $pool->amount = $request->amount;
            }
            else if($pool->type == "Withdraw")
            {
                $diff = $request->amount + $pool->amount;
    
                $pool->amount = $request->amount * (-1);
            }
    
            $pool->note = $request->note;
    
            $pool->save();

    
            $pools = Pool::whereDate('created_at', '>=', $pool->created_at)
            ->whereIn('type', ['Closing Balance', 'Opening Balance'])
            ->get();
    
            foreach($pools as $p)
            {
                $p->amount -= $diff;
                $p->save();
            }

            DB::commit();
    
            return response(['message' => 'Pool Updated Successfully !'], 200);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }

    public function delete($pool_id)
    {
        if(!auth()->user()->hasPermission("pool.delete"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }
        
        DB::beginTransaction();

        try {
            
            $pool = Pool::find($pool_id);

            if($pool->type == "Opening Balance" || $pool->type == "Closing Balance")
            {
                DB::rollBack();

                return response([
                    'errors' => [
                        'message' => ['Cannot modify Opening/Closing balance !']
                    ]
                ], 409);
            }
    
            $pools = Pool::whereDate('created_at', '>=', $pool->created_at)
            ->whereIn('type', ['Closing Balance', 'Opening Balance'])
            ->get();
    
            foreach($pools as $p)
            {
                $p->amount -= $pool->amount;
                $p->save();
            }
    
            $pool->delete();

            DB::commit();
    
            return response(['message' => 'Pool Deleted Successfully !'], 200);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }


}

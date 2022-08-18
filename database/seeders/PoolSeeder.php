<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pool;
use Carbon\Carbon;

class PoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $opening_balance = Pool::where('type', '=', 'Opening Balance')->first();

        if($opening_balance == null)
        {
            $opening_balance = new Pool();
            
            $opening_balance->type = "Opening Balance";
            $opening_balance->amount = 0.00;
            $opening_balance->note = "N/A";
            
            $opening_balance->save();

            $date = Carbon::today();
            
            $start = $date->year . "-" . $date->month . "-01";  

            $opening_balance->created_at = $start;

            $opening_balance->save();
        }
    }
}

<?php

namespace App\Services\Record;

use App\Models\Record as REC;
use Carbon\Carbon;

class Record
{
    public function store($rec_data_arr)
    {
        $rec = new REC();

        $rec->category = $rec_data_arr['category'];

        $rec->type = $rec_data_arr['type'];

        $rec->reference_id = $rec_data_arr['reference_id'];

        $rec->cash_flow = $rec_data_arr['cash_flow'];

        $rec->amount = $rec_data_arr['amount'];

        $rec->finalized_by = auth()->user()->id;

        $rec->finalized_at = Carbon::now();

        $rec->save();
    }
}
<?php

namespace App\Services\Record;

use Illuminate\Support\Facades\Facade;

class RecordFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Record';
    }
}
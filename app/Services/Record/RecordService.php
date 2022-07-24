<?php

namespace App\Services\Record;

use App\Services\Record\Record;

class RecordService
{
    public static function Handle($app)
    {
        $app->bind('Record', function() {

            return new Record();

        });
    }
}
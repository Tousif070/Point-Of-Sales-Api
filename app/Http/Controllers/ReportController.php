<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CAS;

class ReportController extends Controller
{
    public function casIndex()
    {
        return CAS::get(318);
    }
}

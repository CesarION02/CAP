<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class configController extends Controller
{
    public function index()
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();
        return view('config.index', compact('config'));
    }
}

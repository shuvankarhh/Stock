<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class CheckController extends Controller
{
    public function index()
    {
        //dd(base_path('index'));
        
        return view('check.index');
    }

    public function store()
    {
        
    }

}

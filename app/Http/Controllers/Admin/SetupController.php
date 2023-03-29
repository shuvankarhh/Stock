<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Client;

class SetupController extends Controller
{
    /**
     * Display a listing of the prducts.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(empty($request->session()->get('client_id')) ){
            return redirect()->route('step-one.create');
        }

        $client = Client::where('Client_ID', $request->session()->get('client_id'))->first();
        $project = Project::where('Project_ID', $request->session()->get('project_id'))->first();

        return view('setup.index', compact('client', 'project'));
    }
}

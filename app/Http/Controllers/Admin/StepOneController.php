<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Client;

class StepOneController extends Controller
{

    /**
     * Show the step One Form for creating a new product.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $clients = Client::with('company')->whereHas('userClient', function($query){
            $query->where('User_ID', auth()->id());
        })->get()->pluck('company.Company_Name', 'Client_ID')->prepend('Please select', '')->all();
        return view('setup.step_one', compact('clients'));
    }

    /**
     * Post Request to store step1 info in session
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required',
        ]);
        $request->session()->put('client_id', $request->get('client_id'));
        $request->session()->forget('project_id');

        return redirect()->route('step-two.create');
    }
}

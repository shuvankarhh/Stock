<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Client;

class StepTwoController extends Controller
{
    /**
     * Show the step One Form for creating a new product.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!\Session::has('client_id')) {
            return redirect()->route('step-one.create');
        }

        $clientID = $request->session()->get('client_id');

        $c = Client::whereHas('company', function ($q) {
            $q->where('Company_Name', '=', '20th Century Props');
        })->first();

        $projects = Project::where('Client_ID', $clientID)->pluck('Project_Name', 'Project_ID')->prepend('Please select', '')->all();

        return view('setup.step_two', compact('projects'));
    }

    /**
     * Show the step One Form for creating a new product.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
        ]);

        $request->session()->put('project_id', $request->get('project_id'));

        // Goes to the check route.
        return redirect()->route('check.index');
    }
}

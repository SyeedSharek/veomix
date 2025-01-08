<?php

namespace App\Http\Controllers;

use App\Models\BranchGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BranchGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::check()){
            $data = BranchGroup::latest()->get();
            return response()->json([
                'message' => 'Data get successfully',
                'data' => $data,
             ], 200);

        }
        else{
            return response()->json([
                'message' => 'Unauthorized',
             ], 401);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(Auth::check()){


            $validator = Validator::make($request->all(),[
                'group_name'=> 'required|string',
                'employee_id'=>'required|integer',
                'groupManager_id'=>'required|integer',
                'opening_date'=>'required|date',
                'country_id'=>'required|integer',
                'division_id'=>'required|integer',
                'distric_id'=>'required|integer',
                'upozila'=>'required',
                'union'=>'required',
                'villageName'=>'required|string',
                'address'=>'string',
                'status'=>'required|boolean',

            ]);

            if($validator->fails()){
                return response()->json([
                   'message' => 'Validation errors',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $request->all();
            $data['opening_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->opening_date)->format('Y-m-d');
            BranchGroup::create($data);
            return response()->json([
               'message' => 'Data stored successfully!!',
                'data' => $data,
            ]);

        }
        else{
            return response()->json([
                'message' => 'Unauthorized',
             ], 401);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

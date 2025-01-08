<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::check()){
            $data = Project::latest()->get();
           return response()->json([
            'message' => 'Data get successfully',
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
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
           if(Auth::check()){
            $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:projects',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }



        $data = $request->all();


        $store_data = Project::create($data);

        return response()->json([
            'message' => 'Data stored successfully!!',
            'data' => $store_data,
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
    public function show(Project $project)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if(Auth::check()){
            $project = Project::find($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:projects,name,',
                'status'  => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // dd($request->all());

            $data = $request->all();

            $project->update($data);

            return response()->json([
                'message' => 'Data updated successfully!!',
                'data' => $project,
            ]);

        }
        else{
            return response()->json([
               'message' => 'Unauthorized',
            ], 401);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if(Auth::check()){
            $project = Project::find($id);
            $project->delete();

            return response()->json([
                'message' => 'Data deleted successfully!!',
            ]);
        }
        else{
            return response()->json([
               'message' => 'Unauthorized',
            ], 401);
        }

    }

}

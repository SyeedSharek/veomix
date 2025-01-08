<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Designation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DesignationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::check()){
            $data = Designation::latest()->get();
            return response()->json([
                'message' => 'Data get successfully',
                'data' => $data,
            ]);

        }else{
            return response()->json([
               'message' => 'Unauthenticated',
            ], 401);
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::check()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:designations',
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

            $store_data = Designation::create($data);

            return response()->json([
                'message' => 'Data stored successfully!!',
                'data' => $store_data,
            ]);
        }
        else{
            return response()->json([
               'message' => 'Unauthorized access',
            ], 401);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Designation $designation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Designation $designation) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        if (Auth::check()) {
            $designation = Designation::find($id);
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:designations,name,' . $designation->id,
                'status'  => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $request->all();

            $designation->update($data);

            return response()->json([
                'message' => 'Data updated successfully!!',
                'data' => $designation,
            ]);
            }else{
                return response()->json([
                   'message' => 'Unauthorized access',
                ], 401);
            }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (Auth::check()) {
            $designation = Designation::find($id);
            $designation->delete();
            return response()->json([
               'message' => 'Data deleted successfully!!',
            ]);

        }else{
            return response()->json([
               'message' => 'Unauthorized access',
            ], 401);
        }


    }
}

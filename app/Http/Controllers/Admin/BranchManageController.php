<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BranchManage;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BranchManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $data = BranchManage::with('employee')->latest()->get();
            return response()->json([
                'message' => 'Data get successfully',
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthenticated',
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
        if (Auth::check()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'region_id' => 'required|integer',
                'employee_id' => 'required',
                'openingDate' => 'required|date',
                'country_id' => 'required|integer',
                'division_id' => 'required|integer',
                'district_id' => 'required|integer',
                'upozila' => 'required',
                'union' => 'required',
                'managerPhone' => 'required|string',
                'address' => 'required|string',
                'status' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $request->all();

            $store_data = BranchManage::create($data);

            return response()->json([
                'message' => 'Data stored successfully!!',
                'data' => $store_data,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized Access',
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
    public function update(Request $request, $id)
    {
        if (Auth::check()) {

            $branchManage = BranchManage::find($id);


            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'region_id' => 'required|integer',
                'managerName' => 'required',
                'openingDate' => 'required|date',
                'country_id' => 'required|integer',
                'division_id' => 'required|integer',
                'district_id' => 'required|integer',
                'upozila' => 'required',
                'union' => 'required',
                'managerPhone' => 'required|string',
                'address' => 'string',
                'status' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $request->all();
            $branchManage->update($data);

            return response()->json([
                'message' => 'Data updated successfully!',
                'data' => $branchManage,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized Access',
            ], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (Auth::check()) {
            $branchManage = BranchManage::find($id);
            $branchManage->delete();

            return response()->json([
                'message' => 'Data deleted successfully!!',
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized Access',
            ], 401);
        }
    }

    public function branchSearch(Request $request)
    {
        if (Auth::check()) {
        } else {
            return response()->json([
                'message' => 'Unauthorized Access',
            ], 401);
        }
    }

    public function branchList(Request $request) {}



}

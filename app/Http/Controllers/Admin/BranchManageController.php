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
            $data = BranchManage::with('employee','country','division','district','rigionalOffice')->latest()->paginate(10);
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
                'name' => 'required|string|unique:branch_manages,name',
                'regionalOffice_id' => 'required|integer',
                'employee_id' => 'required|integer|unique:branch_manages,member_id',
                'openingDate' => 'required|date',
                'country_id' => 'required|integer',
                'division_id' => 'required|integer',
                'district_id' => 'required|integer',
                'upozila' => 'required',
                'union' => 'required',
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
                'name' => 'required|string|unique:branch_manages,name,' . $id,
                'regionalOffice_id' => 'required|integer|unique:branch_manages,employee_id',
                'managerName' => 'required',
                'openingDate' => 'required|date',
                'country_id' => 'required|integer',
                'division_id' => 'required|integer',
                'district_id' => 'required|integer',
                'upozila' => 'required',
                'union' => 'required',
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

    public function bracnchSearch(Request $request)
    {
        if (Auth::check()) {
            $search = $request->input('search');

            $branchManage = BranchManage::with('employee')->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('openingDate', 'LIKE', "%{$search}%")
                            ->latest()
                            ->paginate(10);

            return response()->json([
                'message' => 'Data get successfully',
                'data' => $branchManage,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized Access',
            ], 401);
        }
    }

    public function branchNameWishShow($branchManage_id){
        if(Auth::check()){
            $branchManage = BranchManage::with('employee')->where('id',$branchManage_id)->first();
            return response()->json([
               'message' => 'Data found successfully',
                'data' => $branchManage,
            ]);

        }
        else{
            return response()->json([
               'message' => 'Unauthorized Access',
            ], 401);
        }

    }






    public function branchList(Request $request) {
        if(Auth::check()){

            $employee_Id = request('employee_id');
            $opening_date = request('opening_date');
            $status = request('status');
            $country_id = request('country_id');
            $district_id = request('district_id');
            $upozila = request('upozila');
            $branchName = request('branch_name');
            $branch_id = request('branch_id');


            $data = BranchManage::where(function ($query) use ($employee_Id, $opening_date, $status, $country_id, $district_id, $upozila,$branchName, $branch_id) {
                if (!empty($employee_Id)) {
                    $query->Where('employee_id',  $employee_Id);
                }
                if (!empty($country_id)) {
                    $query->Where('country_id',  $country_id);
                }
                if (!empty($district_id)) {
                    $query->Where('division_id',  $district_id);
                }
                if (!empty($upozila)) {
                    $query->Where('upozila',  'LIKE', '%' . $upozila . '%');
                }

                if (!empty($opening_date)) {
                    $query->Where('openingDate', 'LIKE', '%' . $opening_date . '%');
                }
                if (!empty($branchName)) {
                    $query->Where('name', 'LIKE', '%' . $branchName . '%');
                }

                if (!empty($branch_id)) {
                    $query->Where('id', 'LIKE', '%' . $branch_id . '%');
                }

                if (!empty($status)) {
                    $query->Where('status',  $status);
                }
            })
                ->with(['employee'])
                ->latest()
                ->paginate(10);


            return response()->json([
                'message' => 'Data retrieved successfully',
                'status' => true,
                'data' => $data,
            ], 200);
        } else {
            return response()->json([

                'error' => 'Unathorized',

            ]);
        }







    }

    public function allBranch(){
        if(Auth::check()){
            $data = BranchManage::all();
            return response()->json([
               'message' => 'Data retrieved successfully',
                'data' => $data,
            ]);
        } else {
            return response()->json([
               'message' => 'Unauthorized Access',
            ], 401);
        }
    }




}

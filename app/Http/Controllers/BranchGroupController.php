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
        if (Auth::check()) {
            $data = BranchGroup::with('employee', 'member', 'country', 'division', 'district')->latest()->paginate(10);
            return response()->json([
                'message' => 'Data get successfully',
                'data' => $data,
            ], 200);
        } else {
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
        if (Auth::check()) {


            $validator = Validator::make($request->all(), [
                'group_name' => 'required|string|unique:branch_groups,group_name',
                'employee_id' => 'required|integer',
                'member_id' => 'required|integer|unique:branch_groups,member_id',
                'openDate' => 'required|date_format:d/m/Y',
                'country_id' => 'required|integer',
                'division_id' => 'required|integer',
                'distric_id' => 'required|integer',
                'upozila' => 'required',
                'union' => 'required',
                'villageName' => 'required|string',
                'address' => 'string',
                'status' => 'required|boolean',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $formattedJoingDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->openDate)->format('Y-m-d');
            $data = $request->all();
            $data['openDate'] = $formattedJoingDate;
            BranchGroup::create($data);
            return response()->json([
                'message' => 'Data stored successfully!!',
                'data' => $data,
            ]);
        } else {
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


    public function update(Request $request, $group_id)
    {
        if (Auth::check()) {
            // Find the group by ID
            $branchGroup = BranchGroup::find($group_id);

            // Check if the group exists
            if (!$branchGroup) {
                return response()->json([
                    'message' => 'Group not found',
                ], 404);
            }

            // Validate the input data
            $validator = Validator::make($request->all(), [
                'group_name' => 'required|string|unique:branch_groups,group_name,' . $group_id,
                'employee_id' => 'required|integer',
                'member_id' => 'required|integer|unique:branch_groups,member_id,' . $group_id,
                'openDate' => 'required|date_format:d/m/Y',
                'country_id' => 'required|integer',
                'division_id' => 'required|integer',
                'distric_id' => 'required|integer',
                'upozila' => 'required|string',
                'union' => 'required|string',
                'villageName' => 'required|string',
                'address' => 'nullable|string',
                'status' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Format the openDate to the correct database format
            $data = $request->all();
            $data['openDate'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->openDate)->format('Y-m-d');

            // Update the group with the formatted data
            $branchGroup->update($data);

            return response()->json([
                'message' => 'Group updated successfully!',
                'data' => $branchGroup,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($group_id)
    {
        if (Auth::check()) {
            $branchGroup = BranchGroup::find($group_id);

            if ($branchGroup) {
                $branchGroup->delete();
                return response()->json([
                    'message' => 'Data deleted successfully!!',
                ]);
            } else {
                return response()->json([
                    'message' => 'Group not found!!',
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    public function searchMemberId($member_id)
    {
        if (Auth::check()) {
            $branchGroup = BranchGroup::with('member')->where('member_id', $member_id)->first();

            if ($branchGroup) {
                return response()->json([
                    'data' => $branchGroup,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Group not found!!',
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    public function groupSearchAll(Request $request)
    {
        if (Auth::check()) {
            $search = $request->search;
            $data = BranchGroup::where('group_name', 'LIKE', '%' . $search . '%')
                ->orWhere('openDate', 'LIKE', '%' . $search . '%')
                ->orWhere('villageName', 'LIKE', '%' . $search . '%')
                ->with('member')
                ->latest()
                ->paginate(10);

            return response()->json([
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    public function groupList()
{
    if (Auth::check()) {
        $group_name = request('group_name');
        $member_id = request('member_id');
        $openDate = request('openDate');
        $status = request('status');
        $branch_manage_id = request('branch_manage_id');
        $employee_id = request('employee_id');
        $group_id = request('group_id');

        $data = BranchGroup::where(function ($query) use ($group_name, $member_id, $openDate, $status, $branch_manage_id, $employee_id, $group_id) {

            if (!empty($group_name)) {
                $query->where('group_name', 'LIKE', '%' . $group_name . '%');
            }

            if (!empty($member_id)) {
                $query->where('member_id', $member_id);
            }

            if (!empty($branch_manage_id)) {
                // Correcting the relationship name from 'branch_groups' to 'employee'
                $query->whereHas('employee', function ($subQuery) use ($branch_manage_id) {
                    $subQuery->where('branch_manage_id', $branch_manage_id);
                });
            }
            if (!empty($employee_id)) {
                $query->where('employee_id', $employee_id);
            }

            if (!empty($group_id)) {
                $query->where('id', $group_id);
            }

            if (!empty($openDate)) {
                $query->where('openDate', 'LIKE', '%' . $openDate . '%');
            }

            if (!empty($status)) {
                $query->where('status', $status);
            }
        })
        ->with(['employee']) // Load employee relation
        ->latest()
        ->paginate(10);

        return response()->json([
            'message' => 'Data retrieved successfully',
            'status' => true,
            'data' => $data,
        ], 200);
    } else {
        return response()->json([
            'message' => 'Unauthorized Access',
        ], 401);
    }
}

public function searchEmployeeId($employee_id){
    if(Auth::check()){
        $data = BranchGroup::with('employee','member')->where('employee_id', $employee_id)->first();
        return response()->json([
           'message' => 'Data retrieved successfully',
            'data' => $data,
        ], 200);
    }else{
        return response()->json([
           'message' => 'Unauthorized Access',
        ], 401);
    }
}



}

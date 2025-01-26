<?php

namespace App\Http\Controllers\EmployeeManagement;

use App\Http\Controllers\Controller;
use App\Models\EmployeeLeave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmployeeLeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $data = EmployeeLeave::with(['employee','branch'])->latest()->paginate(10);
            return response()->json([
                'message' => 'Data get successfully',
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthenticated'
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
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer|exists:employees,id',
            'leave_days' => 'required|integer',
            'leave_reason' => 'required|string',
            'leave_start_date' => 'required|date_format:d/m/Y',
            'leave_end_date' => 'required|date_format:d/m/Y',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Convert dates from d/m/Y to Y-m-d
        $request->merge([
            'leave_start_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->leave_start_date)->format('Y-m-d'),
            'leave_end_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->leave_end_date)->format('Y-m-d'),
        ]);

        // Calculate total leave
        $existingLeave = EmployeeLeave::where('employee_id', $request->employee_id)->sum('leave_days');
        //  dd( $existingLeave);

        $totalLeave = $existingLeave > 0 ? $existingLeave + $request->leave_days : $request->leave_days;
        //  dd($totalLeave);

        // Create the leave request with total_leave included
        $leave = EmployeeLeave::create([
            'employee_id' => $request->employee_id,
            'leave_days' => $request->leave_days,
            'total_leave' => $totalLeave,
            'leave_reason' => $request->leave_reason,
            'leave_start_date' => $request->leave_start_date,
            'leave_end_date' => $request->leave_end_date,
        ]);

        return response()->json([
            'message' => 'Leave request submitted successfully!',
            'leave' => $leave,
        ], 201);
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

    public function update(Request $request, $employeeLeave_id)
    {
        if (Auth::check()) {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|integer|exists:employees,id',
                'leave_days' => 'required|integer',
                'leave_reason' => 'required|string',
                'leave_start_date' => 'required|date_format:d/m/Y',
                'leave_end_date' => 'required|date_format:d/m/Y',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Convert dates from d/m/Y to Y-m-d
            $request->merge([
                'leave_start_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->leave_start_date)->format('Y-m-d'),
                'leave_end_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->leave_end_date)->format('Y-m-d'),
            ]);

            $leave = EmployeeLeave::find($employeeLeave_id);
            if ($leave) {
                // Calculate total leave
                $existingLeave = EmployeeLeave::where('employee_id', $request->employee_id)->sum('leave_days');

                // Adjust for the old leave_days before updating
                $totalLeave = $existingLeave - $leave->leave_days + $request->leave_days;

                // Update the record
                $leave->update([
                    'employee_id' => $request->employee_id,
                    'leave_days' => $request->leave_days,
                    'total_leave' => $totalLeave,
                    'leave_reason' => $request->leave_reason,
                    'leave_start_date' => $request->leave_start_date,
                    'leave_end_date' => $request->leave_end_date,
                ]);

                return response()->json([
                    'message' => 'Leave request updated successfully!',
                    'leave' => $leave,
                ]);
            }

            return response()->json([
                'message' => 'Leave request not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Unauthenticated',
        ], 401);
    }





    public function destroy($employeeLeave_id)
    {
        if (Auth::check()) {
            $leave = EmployeeLeave::find($employeeLeave_id);

            if ($leave) {
                $leave->delete();
                return response()->json([
                    'message' => 'Leave request deleted successfully!'
                ]);
            } else {
                return response()->json([
                    'message' => 'Leave request not found!'
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }
    }


    public function employeeWishList($employeeLeave_id){

        if(Auth::check()){
            $leave = EmployeeLeave::with('employee')->where('employee_id', $employeeLeave_id)->get();
            return response()->json([
               'message' => 'Data get successfully',
                'data' => $leave,
            ]);
        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
            ], 401);
        }
    }

    public function searchEmployeeLeave(Request $request){
        if(Auth::check()){
            $search = $request->input('search');

            $leave = EmployeeLeave::with(['employee'])
            ->whereHas('employee', function ($query) use ($search) {
                $query->where('employeeName', 'like', '%' . $search . '%');
            })
            ->orWhere('leave_start_date', 'like', '%' . $search . '%')
            ->orWhere('leave_end_date', 'like', '%' . $search . '%')
            ->latest()
            ->paginate(10);

            return response()->json([
               'message' => 'Data get successfully',
                'data' => $leave,
            ]);

        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
            ], 401);
        }

    }




    public function employeeLeaveBrachID($branch_id){
        if(Auth::check()){
            $leave = EmployeeLeave::with(['employee'])
            ->whereHas('employee', function ($query) use ($branch_id) {
                $query->where('branch_manage_id', $branch_id);
            })
            ->latest()
            ->paginate(10);
            return response()->json([
               'message' => 'Data get successfully',
                'data' => $leave,
            ]);
        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
            ], 401);
        }
    }













}

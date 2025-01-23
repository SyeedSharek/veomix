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
        //
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
        // Assuming authentication is handled via middleware
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

        $existingLeave = EmployeeLeave::where('employee_id', $request->employee_id)->sum('leave_days');


        $totalLeave = $existingLeave + $request->leave_days;

        $leave = EmployeeLeave::create($request->only([
            'employee_id',
            'leave_days',
            'leave_reason',
            'leave_start_date',
            'leave_end_date',

        ]));

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

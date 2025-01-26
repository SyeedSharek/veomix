<?php

namespace App\Http\Controllers\EmployeeManagement;

use App\Http\Controllers\Controller;
use App\Models\SalaryDisbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SalaryDisbursmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $data = SalaryDisbursement::with(['employee', 'month', 'branch'])->latest()->paginate(10);

            return response()->json([
                'message' => 'Data fetched successfully',
                'data' => $data,
            ]);
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
            $entry_by = Auth::user();
            $entry_name = $entry_by->name;


            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|integer|exists:employees,id',
                'month_id' => 'required|integer|exists:months,id',
                'basicSalary' => 'required|numeric',
                'houseRent' => 'required|numeric',
                'ta' => 'required|numeric',
                'da' => 'required|numeric',
                'festivalBonus' => 'required|numeric',
                'providentFund' => 'required|numeric',


            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ], 400);
            }

            $totalSalary = (float)$request->basicSalary +
                (float)$request->houseRent +
                (float)$request->ta +
                (float)$request->da +
                (float)$request->festivalBonus +
                (float)$request->providentFund;

            $data = $request->all();
            $data['totalSalary'] = $totalSalary;
            $data['salaryFromDate'] = now();
            $data['salaryPayDate'] = now();
            $data['entry_by'] = $entry_name;

            $data = SalaryDisbursement::create($data);

            return response()->json([
                'message' => 'Salary Disbursement Created Successfully',
                'data' => $data,
            ], 201);
        } else {
            return response()->json([
                'error' => 'Unathorized',
            ]);
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
    public function update(Request $request, $salaryDisbutsment_id)
    {
        if (Auth::check()) {
            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|integer|exists:employees,id',
                'month_id' => 'required|integer|exists:months,id',
                'basicSalary' => 'required|numeric',
                'houseRent' => 'required|numeric',
                'ta' => 'required|numeric',
                'da' => 'required|numeric',
                'festivalBonus' => 'required|numeric',
                'providentFund' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors occurred.',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $salaryDisbursement = SalaryDisbursement::findOrFail($salaryDisbutsment_id);

            $totalSalary = (float)$request->basicSalary +
                (float)$request->houseRent +
                (float)$request->ta +
                (float)$request->da +
                (float)$request->festivalBonus +
                (float)$request->providentFund;

            $data = $request->all();

            $data['totalSalary'] = $totalSalary;

            $salaryDisbursement->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Salary Disbursement updated successfully',
                'data' => $salaryDisbursement,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($salaryDisbutsment_id)
    {
        if (Auth::check()) {

            SalaryDisbursement::destroy($salaryDisbutsment_id);
            return response()->json([
                'message' => 'Salary Disbursement deleted successfully',
            ]);
        } else {
            return response()->json([
                'error' => 'Unathorized',
            ]);
        }
    }


    public function employeeSalaryBranchID($branch_id)
    {
        if (Auth::check()) {
            $leave = SalaryDisbursement::with(['employee', 'month'])
                ->whereHas('employee', function ($query) use ($branch_id) {
                    $query->where('branch_manage_id', $branch_id);
                })
                ->latest()
                ->paginate(10);
            return response()->json([
                'message' => 'Data get successfully',
                'data' => $leave,
            ]);
        } else {
            return response()->json([
                'error' => 'Unathorized',
            ]);
        }
    }


    public function searchEmployeeSalaryDisbursement(Request $request)
    {
        if (Auth::check()) {
            $search = $request->input('search');

            $leave = SalaryDisbursement::with(['employee', 'month'])
                ->whereHas('employee', function ($query) use ($search) {
                    $query->where('employeeName', 'like', '%' . $search . '%');
                })
                ->latest()
                ->paginate(10);

            return response()->json([
                'message' => 'Data get successfully',
                'data' => $leave,
            ]);
        } else {
            return response()->json([
                'error' => 'Unathorized',
            ]);
        }
    }


    public function searchEmployeeSalaryidWish(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $employee_name_id = $request->input('employee_name_id');
        $employee_id = $request->input('employee_id');
        $month_id = $request->input('month_id');
        $employee_joining_date = $request->input('employee_joining_date');
        $branch_id = $request->input('branch_id');

        $query = SalaryDisbursement::query();

        if (!empty($employee_name_id)) {
            $query->where('employee_id', $employee_name_id);
        }

        if (!empty($employee_id)) {
            $query->where('employee_id', $employee_id);
        }

        if (!empty($month_id)) {
            $query->where('month_id', $month_id);
        }

        if (!empty($branch_id)) {
            $query->whereHas('employee', function ($subQuery) use ($branch_id) {
                $subQuery->where('branch_manage_id', $branch_id);
            });
        }

        if (!empty($employee_joining_date)) {
            $query->whereHas('employee', function ($subQuery) use ($employee_joining_date) {
                $subQuery->where('joingDate', $employee_joining_date);
            });
        }

        $data = $query->with(['employee', 'month'])->latest()->paginate(10);

        if ($data->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No records found for the given criteria.',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data retrieved successfully',
            'data' => $data,
        ], 200);
    }














}

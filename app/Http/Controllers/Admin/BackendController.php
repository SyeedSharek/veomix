<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;
use App\Models\Employee;
use App\Models\Divisionoffice;
use App\Models\Branchoffice;
use Illuminate\Support\Facades\Validator;

class BackendController extends Controller
{

    public function getDivisions($id){
        $data = Division::where('country_id', $id)
                ->whereStatus(1)
                ->orderBy('name', 'asc')
                ->get();
        return response()->json([
            'message' => 'Data get successfully',
            'data' => $data,
        ]);
    }

    public function getDistricts($id){
        $data = District::where('division_id', $id)
                ->whereStatus(1)
                ->orderBy('name', 'asc')
                ->get();
        return response()->json([
            'message' => 'Data get successfully',
            'data' => $data,
        ]);
    }

    public function getUpazilas($id){
        $data = Upazila::where('district_id', $id)
                ->whereStatus(1)
                ->orderBy('name', 'asc')
                ->get();
        return response()->json([
            'message' => 'Data get successfully',
            'data' => $data,
        ]);
    }

    public function getUnions($id){
        $data = Union::where('upazila_id', $id)
                ->whereStatus(1)
                ->orderBy('name', 'asc')
                ->get();
        return response()->json([
            'message' => 'Data get successfully',
            'data' => $data,
        ]);
    }

    // get Project wise Manager List(employee)
    public function getManagers($id){
        $data = Employee::with(['designation' => function ($query) {
                        $query->where('id', 1)
                              ->where('status', 1)
                              ->select('id', 'name'); // Fetch only id and name fields
                    }])
                    ->select('id', 'employee_name', 'designation_id')
                    ->where('project_id', $id)
                    ->whereStatus(1)
                    ->latest()
                    ->get();
        return response()->json([
            'message' => 'Data get successfully',
            'data' => $data,
        ]);
    }

    // get Division Office wise Division
    public function getOfficeWiseDivision($id){
        $data = Divisionoffice::with(['division' => function ($query) {
                        $query->where('status', 1)
                              ->select('id', 'name', 'country_id'); // Fetch only id and name fields
                    }])
                    ->select('id', 'office_name', 'division_id')
                    ->where('id', $id)
                    ->whereStatus(1)
                    ->latest()
                    ->get();
        return response()->json([
            'message' => 'Data get successfully',
            'data' => $data,
        ]);
    }

    // get abailable branch manager
    public function getAvailableBranchManager(){
        $employeeIds = Branchoffice::pluck('employee_id')->toArray();
        // dd($employeeIds);
        $data = Employee::whereNotIn('id', $employeeIds)->where('designation_id', 3)->whereStatus(1)->select('id', 'employee_name', 'mobile_number')->latest()->get();
        // dd($data->toArray());

        return response()->json([
            'message' => 'Data get successfully',
            'data' => $data,
        ]);
    }

    // get abailable division manager
    public function getAvailableManager(){
        $employeeIds = Divisionoffice::pluck('employee_id')->toArray();
        // dd($employeeIds);
        $data = Employee::whereNotIn('id', $employeeIds)->where('designation_id', 1)->whereStatus(1)->select('id', 'employee_name', 'mobile_number')->latest()->get();
        // dd($data->toArray());

        return response()->json([
            'message' => 'Data get successfully',
            'data' => $data,
        ]);
    }




}

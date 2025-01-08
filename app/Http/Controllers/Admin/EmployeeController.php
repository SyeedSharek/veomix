<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Http\Traits\NormalImageUpload;
// use App\Http\Traits\ImageUpload;
use Illuminate\Support\Facades\Validator;
use File;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{

    use NormalImageUpload;
    // use ImageUpload;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $data = Employee::with(['designation','branchName'])->latest()->get();

            return response()->json([
                'message' => 'Data retrieved successfully',
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
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'project_id' => 'required|integer|exists:projects,id',
    //         'employee_name' => 'required|string|max:255',
    //         'emp_id' => 'required|integer|digits:6|unique:employees,emp_id',
    //         'father_name' => 'required|string|max:255',
    //         'gender' => 'required|string|in:male,female,other',
    //         'joining_date' => 'required|date',
    //         'employee_id' => 'nullable|required_with:branch_id|integer|exists:employees,id',
    //         'national_id' => 'required|numeric|digits_between:8,20|unique:employees,national_id',
    //         'date_of_birth' => 'required|date',
    //         // 'date_of_birth' => 'required|date_format:Y-m-d',
    //         'religion' => 'required|string|max:255',
    //         'branch_id' => 'nullable|required_if:designation_id,2|integer|exists:branches,id',
    //         'education_id' => 'required|integer|exists:education,id',
    //         'designation_id' => 'required|integer|exists:designations,id',
    //         'blood_group' => 'required|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
    //         'email' => 'required|string|email|max:255|unique:employees,email',
    //         'marital_status' => 'required|string|in:single,married,divorced,widowed',
    //         'present_address' => 'required|string',
    //         'permanent_address' => 'required|string',
    //         'emergency_number' => 'required|string|max:255',
    //         'mobile_number' => 'required|string|max:255|unique:employees,mobile_number',
    //         'image' => 'nullable|image|mimes:webp,jpeg,png,jpg|max:2048',
    //         'signature' => 'nullable|image|mimes:webp,jpeg,png,jpg|max:2048',
    //         'status' => 'required|boolean',
    //     ], [
    //         'branch_id.required_if' => 'Branch ID is required when designation ID is 2.',
    //         'employee_id.required_with' => 'Manager ID is required when branch ID is provided.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'message' => 'Validation errors',
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     $data = $request->all();

    //     $data['emp_id'] = 'Veomix-'.$request->emp_id;

    //     // dd($request->all());
    //     // return $request->all();

    //     // image
    //     $data['image'] = $this->uploadImage($request, 'image', 'employee_image-', 'employee_images');

    //     // signature
    //     $data['signature'] = $this->uploadImage($request, 'signature', 'employee_signature-', 'employee_signatures');

    //     // dd($data['image']);
    //     // dd($request->all());

    //     $store_data = Employee::create($data);

    //     return response()->json([
    //         'message' => 'Data stored successfully!!',
    //         'data' => $store_data,
    //     ]);
    // }

    public function store(Request $request){


        if(Auth::check()){

            $request->merge([
                'joingDate' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->joingDate)->format('Y-m-d'),
                'dateOfBirth' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->dateOfBirth)->format('Y-m-d'),
            ]);

            $validator = Validator::make($request->all(),[
                'employeeName' =>'required|string',
                'employeeId' =>'required|string|max:255',
                'fatherName' =>'required',
                'joingDate' =>'required|date',
                'managerName' =>'required|string',
                'nationalId' =>'required',
                'dateOfBirth' => 'required|date',
                'riligion_id' =>'required',
                'branch_manage_id' =>'required|integer',
                'education_id' =>'required|integer',
                'designation_id' =>'required|integer',
                'blood_id' =>'required|integer',
                'gender_id' =>'required|integer',
                'email' =>'required|email',
                'marital_id' =>'required|integer',
                'presentAddress' =>'string',
                'permanentAddress' =>'string',
                'emergencyNumber' =>'required',
                'phoneNumber' =>'required',
                'profilePhoto' =>'required',
                'signaturePhoto' =>'required',
            ]);


            //  dd($validator);


            if($validator->fails()){
                return response()->json([
                   'message' => 'Validation errors',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // $data = $request->all();
            $data = $request->except(['profilePhoto', 'signaturePhoto']);

            if ($request->hasFile('profilePhoto')) {
                $image = $request->file('profilePhoto');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('employee/profile'), $imageName);
                // $imageName = 'employee/profile'. $imageName;
                $data['profilePhoto'] = 'employee/profile/'. $imageName;

            }

            if ($request->hasFile('signaturePhoto')) {
                $image = $request->file('signaturePhoto');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('employee/signature'), $imageName);
                // $imageName = 'employee/signature/'. $imageName;
                $data['signaturePhoto'] = 'employee/signature/'. $imageName;
            }



            $employee = Employee::create($data);
            return response()->json(['message' =>'Successfully created employee', 'data'=>$employee]);


        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
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
            // Find the employee by ID
            $employee = Employee::find($id);

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee not found',
                ], 404);
            }

            // Parse the date inputs to ensure correct format
            if ($request->has('joingDate')) {
                $request->merge([
                    'joingDate' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->joingDate)->format('Y-m-d'),
                ]);
            }

            if ($request->has('dateOfBirth')) {
                $request->merge([
                    'dateOfBirth' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->dateOfBirth)->format('Y-m-d'),
                ]);
            }

            // Validate the inputs
            $validator = Validator::make($request->all(), [
                'employeeName' => 'required|string',
                'employeeId' => 'required|string|max:255',
                'fatherName' => 'required',
                'joingDate' => 'required|date',
                'managerName' => 'required|string',
                'nationalId' => 'required',
                'dateOfBirth' => 'required|date',
                'riligion_id' => 'required',
                'branch_manage_id' => 'required|integer',
                'education_id' => 'required|integer',
                'designation_id' => 'required|integer',
                'blood_id' => 'required|integer',
                'gender_id' => 'required|integer',
                'email' => 'required|email',
                'marital_id' => 'required|integer',
                'presentAddress' => 'nullable|string',
                'permanentAddress' => 'nullable|string',
                'emergencyNumber' => 'required',
                'phoneNumber' => 'required',
                'profilePhoto' => 'nullable|image',
                'signaturePhoto' => 'nullable|image',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Prepare the data for update
            $data = $request->except(['profilePhoto', 'signaturePhoto']);

            // Handle profile photo update
            if ($request->hasFile('profilePhoto')) {
                // Delete the old photo if it exists
                if ($employee->profilePhoto && file_exists(public_path($employee->profilePhoto))) {
                    unlink(public_path($employee->profilePhoto));
                }

                // Save the new profile photo
                $image = $request->file('profilePhoto');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('employee/profile'), $imageName);
                $data['profilePhoto'] = 'employee/profile/' . $imageName;
            }

            // Handle signature photo update
            if ($request->hasFile('signaturePhoto')) {
                // Delete the old signature photo if it exists
                if ($employee->signaturePhoto && file_exists(public_path($employee->signaturePhoto))) {
                    unlink(public_path($employee->signaturePhoto));
                }

                // Save the new signature photo
                $image = $request->file('signaturePhoto');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('employee/signature'), $imageName);
                $data['signaturePhoto'] = 'employee/signature/' . $imageName;
            }

            // Update the employee record
            $employee->update($data);

            return response()->json([
                'message' => 'Employee updated successfully',
                'data' => $employee,
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
    public function destroy($id)
    {


        if(Auth::check()){
            $employee = Employee::find($id);

            if ($employee->profilePhoto && file_exists(public_path($employee->profilePhoto))) {
                unlink(public_path($employee->profilePhoto));
            }

            if ($employee->signaturePhoto && file_exists(public_path($employee->signaturePhoto))) {
                unlink(public_path($employee->signaturePhoto));
            }

            $employee->delete();

        return response()->json([
            'message' => 'Employee deleted successfully',
        ]);


        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
            ], 401);
        }

    }


    public function searchEmployee(Request $request)
    {
        if (Auth::check()) {

            $search = $request->input('search');
            $employees = Employee::with('designation')->where('employeeName', 'LIKE', '%' . $search . '%')
                ->orWhere('managerName', 'LIKE', '%' . $search . '%')
                ->orWhere('phoneNumber', 'LIKE', '%' . $search . '%')
                ->get();

            return response()->json([
                'message' => 'Employees',
                'data' => $employees,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
    }

    public function getEmployeeByBranch(Request $request){
        if (Auth::check()) {

            $branchId = $request->input('branch_manage_id');

            $employees = Employee::with('designation')->where('branch_manage_id', $branchId)->get();

            return response()->json([
               'message' => 'Employees',
                'data' => $employees,
            ]);
        } else {
            return response()->json([
               'message' => 'Unauthenticated',
            ], 401);
        }
    }












}

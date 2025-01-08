<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Divisionoffice;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class DivisionofficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::check()){
            $data = Divisionoffice::latest()->with('division')->get();
            return response()->json([
            'message' => 'Data get successfully',
            'data' => $data,
        ]);

        }
        else{
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
        if(Auth::check()){


            $validator = Validator::make($request->all(), [
            'office_name' => 'required|string|max:255|unique:divisionoffices,office_name', // Corrected validation rule
            'project_id' => 'required|integer|exists:projects,id',
            'manager_name' => 'required|string', // Corrected validation rule
            'opening_date' => 'required|date',
            'country_id' => 'required|integer|exists:countries,id',
            'division_id' => 'required|integer|exists:divisions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'upazila' => 'required|string',
            'union' => 'required|string',
            'manager_phone' => 'required|string|min:11|max:20|exists:employees,mobile_number|unique:divisionoffices,employee_phone', // Corrected validation rule
            'address' => 'required|string',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->all();

        $store_data = Divisionoffice::create($data);

        return response()->json([
            'message' => 'Data stored successfully!!',
            'data' => $store_data,
        ]);
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
    public function update(Request $request, Divisionoffice $divisionoffice)
    {
        // Validate the incoming request data

        if(Auth::check()){
            $validator = Validator::make($request->all(), [
            'office_name' => 'required|string|max:255|unique:divisionoffices,office_name,' . $divisionoffice->id, // Exclude current record from uniqueness check
            'project_id' => 'required|integer|exists:projects,id',
            'employee_id' => 'required|integer|exists:employees,id|unique:divisionoffices,employee_id,' . $divisionoffice->id, // Exclude current record from uniqueness check
            'opening_date' => 'required|date',
            'country_id' => 'required|integer|exists:countries,id',
            'division_id' => 'required|integer|exists:divisions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'upazila_id' => 'required|integer|exists:upazilas,id',
            'union_id' => 'required|integer|exists:unions,id',
            'employee_phone' => 'required|string|min:11|max:20|exists:employees,mobile_number|unique:divisionoffices,employee_phone,' . $divisionoffice->id, // Exclude current record from uniqueness check
            'address' => 'required|string',
            'status' => 'required|boolean',
        ]);

        // If validation fails, return the errors
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update the divisionoffice record with the validated data
        $divisionoffice->update($request->all());

        // Return the updated division office data
        return response()->json([
            'message' => 'Data updated successfully!!',
            'data' => $divisionoffice,
        ]);
        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
            ], 401);
        }


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Divisionoffice $divisionoffice)
    {
        if(Auth::check()){
            $divisionoffice->delete();
        return response()->json([
            'message' => 'Data deleted successfully!!',
        ]);
        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
            ], 401);
        }



    }


}

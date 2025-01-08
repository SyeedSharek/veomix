<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Regionaloffice;
use Illuminate\Support\Facades\Validator;

class RegionalofficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Regionaloffice::latest()->get();
        return response()->json([
            'message' => 'Data get successfully',
            'data' => $data,
        ]);
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
            'office_name' => 'required|string|max:255|unique:regionaloffices,office_name',
            'divisionoffice_id' => 'required|integer|exists:divisionoffices,id',
            'division_id' => 'required|integer|exists:divisions,id',
            'opening_date' => 'required|date',
            'country_id' => 'required|integer|exists:countries,id',
            'district_id' => 'required|integer|exists:districts,id',
            'upazila_id' => 'required|integer|exists:upazilas,id',
            'union_id' => 'required|integer|exists:unions,id',
            'employee_phone' => 'required|string|min:11|max:20|exists:divisionoffices,employee_phone',
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

        $store_data = Regionaloffice::create($data);

        return response()->json([
            'message' => 'Data stored successfully!!',
            'data' => $store_data,
        ]);
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
    public function update(Request $request, Regionaloffice $regionaloffice)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'office_name' => 'required|string|max:255|unique:regionaloffices,office_name,' . $regionaloffice->id,
            'divisionoffice_id' => 'required|integer|exists:divisionoffices,id',
            'division_id' => 'required|integer|exists:divisions,id',
            'opening_date' => 'required|date',
            'country_id' => 'required|integer|exists:countries,id',
            'district_id' => 'required|integer|exists:districts,id',
            'upazila_id' => 'required|integer|exists:upazilas,id',
            'union_id' => 'required|integer|exists:unions,id',
            'employee_phone' => 'required|string|min:11|max:20|exists:divisionoffices,employee_phone',
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
        $regionaloffice->update($request->all());

        // Return the updated division office data
        return response()->json([
            'message' => 'Data updated successfully!!',
            'data' => $regionaloffice,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Regionaloffice $regionaloffice)
    {
        $regionaloffice->delete();
        return response()->json([
            'message' => 'Data deleted successfully!!',
        ]);
    }
}

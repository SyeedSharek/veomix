<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use Illuminate\Support\Facades\Validator;

class UpazilaController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Upazila::latest()->get();
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

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:upazilas',
            'country_id' => 'required|integer|exists:countries,id',
            'division_id' => 'required|integer|exists:divisions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'status'  => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // dd($request->all());

        $data = $request->all();

        $store_data = Upazila::create($data);

        return response()->json([
            'message' => 'Data stored successfully!!',
            'data' => $store_data,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Upazila $upazila)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Upazila $upazila)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Upazila $upazila)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:upazilas,name,'. $upazila->id,
            'country_id' => 'required|integer|exists:countries,id',
            'division_id' => 'required|integer|exists:divisions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'status'  => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // dd($request->all());

        $data = $request->all();

        $upazila->update($data);

        return response()->json([
            'message' => 'Data updated successfully!!',
            'data' => $upazila,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Upazila $upazila)
    {
        $upazila->delete();
        return response()->json([
            'message' => 'Data deleted successfully!!',
        ]);
    }



}

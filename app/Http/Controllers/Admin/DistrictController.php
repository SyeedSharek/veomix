<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Division;
use App\Models\District;
use Illuminate\Support\Facades\Validator;

class DistrictController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = District::latest()->get();
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
            'name' => 'required|string|max:255|unique:districts',
            'country_id' => 'required|integer|exists:countries,id',
            'division_id' => 'required|integer|exists:divisions,id',
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

        $store_data = District::create($data);

        return response()->json([
            'message' => 'Data stored successfully!!',
            'data' => $store_data,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(District $district)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(District $district)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, District $district)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:districts,name,'. $district->id,
            'country_id' => 'required|integer|exists:countries,id',
            'division_id' => 'required|integer|exists:divisions,id',
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

        $district->update($data);

        return response()->json([
            'message' => 'Data updated successfully!!',
            'data' => $district,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(District $district)
    {
        $district->delete();
        return response()->json([
            'message' => 'Data deleted successfully!!',
        ]);
    }


}

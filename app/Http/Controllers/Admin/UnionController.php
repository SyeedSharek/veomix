<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;
use Illuminate\Support\Facades\Validator;

class UnionController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Union::latest()->get();
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
            'name' => 'required|string|max:255|unique:unions',
            'country_id' => 'required|integer|exists:countries,id',
            'division_id' => 'required|integer|exists:divisions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'upazila_id' => 'required|integer|exists:upazilas,id',
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

        $store_data = Union::create($data);

        return response()->json([
            'message' => 'Data stored successfully!!',
            'data' => $store_data,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Union $union)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Union $union)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Union $union)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:unions,name,'. $union->id,
            'country_id' => 'required|integer|exists:countries,id',
            'division_id' => 'required|integer|exists:divisions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'upazila_id' => 'required|integer|exists:upazilas,id',
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

        $union->update($data);

        return response()->json([
            'message' => 'Data updated successfully!!',
            'data' => $union,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Union $union)
    {
        $union->delete();
        return response()->json([
            'message' => 'Data deleted successfully!!',
        ]);
    }


}

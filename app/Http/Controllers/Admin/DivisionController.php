<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Division;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class DivisionController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::check()){

        $data = Division::latest()->get();
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

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        if(Auth::check()){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:divisions',
            'country_id' => 'required|integer|exists:countries,id',
            'status'  => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }


        $data = $request->all();


        $store_data = Division::create($data);

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
    public function show(Division $division)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Division $division)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)

    {
        if(Auth::check()){


            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:divisions,name,'. $id,
                'country_id' => 'required|integer|exists:countries,id',
                'status'  => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                   'message' => 'Validation errors',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $request->all();

            $division = Division::find($id);
            if($division){
                $division->update($data);
                return response()->json([
                   'message' => 'Data updated successfully!!',
                    'data' => $division,
                ]);
            }else{
                return response()->json([
                   'message' => 'Data not found!!',
                ], 404);
            }
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
    public function destroy($id)
    {
        if(Auth::check()){
            $division = Division::find($id);
            if($division){
                $division->delete();
                return response()->json([
                   'message' => 'Data deleted successfully!!',
                ]);
            }
            else{
                return response()->json([
                   'message' => 'Data not found!!',
                ], 404);
            }
        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
            ], 401);
        }
    }


}

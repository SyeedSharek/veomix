<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::check()){
            $data = Country::latest()->get();
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
        // dd('country');
        if(Auth::check()){
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:countries',
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

            $store_data = Country::create($data);

            return response()->json([
                'message' => 'Data stored successfully!!',
                'data' => $store_data,
            ]);

        }
        else{
            return response()->json([
               'message' => 'Unauthorized',
               'status_code' => 401,
            ], 401);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Country $country)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Country $country)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        if(Auth::check()){


            $country = Country::find($id);
            if(!$country){
                return response()->json([
                   'message' => 'Data not found',
                   'status_code' => 404,
                ], 404);
            }
            {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|max:255|unique:countries,name,'.$id,
                    'status'  => 'nullable|boolean',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Validation errors',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                $data = $request->all();

                $country->update($data);

                return response()->json([
                   'message' => 'Data updated successfully!!',
                   'data' => $country,
                ]);
            }

       }else{
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
            $country = Country::find($id);

            if($country){
                $country->delete();
                return response()->json([
                    'message' => 'Data deleted successfully!!',
                ]);
            }
            else{
                return response()->json([
                   'message' => 'Data not found',
                   'status_code' => 404,
                ], 404);
            }


        }
        else{
            return response()->json([
               'message' => 'Unauthorized',
               'status_code' => 401,
            ], 401);
        }

    }



}

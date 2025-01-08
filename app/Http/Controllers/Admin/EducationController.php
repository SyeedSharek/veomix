<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Education;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class EducationController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::check()){
            $data = Education::latest()->get();
        return response()->json([
            'message' => 'Data get successfully',
            'data' => $data,
        ]);

        }
        else{
            return response()->json([
               'message' => 'Unauthorized Access',
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
                'name' => 'required|string|max:255|unique:education',
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

            $store_data = Education::create($data);

            return response()->json([
                'message' => 'Data stored successfully!!',
                'data' => $store_data,
            ]);

        }
        else{
            return response()->json([
               'message' => 'Unauthorized Access',
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
        if(Auth::check()){
            $education = Education::find($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:education,name,'. $education->id,
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

            $education->update($data);

            return response()->json([
                'message' => 'Data updated successfully!!',
                'data' => $education,
            ]);

        }else{
            return response()->json([
               'message' => 'Unauthorized Access',
            ], 401);
        }


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if(Auth::check()){
            $education = Education::find($id);

            if($education){
                $education->delete();
                return response()->json([
                    'message' => 'Data deleted successfully!!',
                ]);
            }
            else{
                return response()->json([
                   'message' => 'Data not found',
                ], 404);
            }
        }
        else{
            return response()->json([
               'message' => 'Unauthorized Access',
            ], 401);
        }
    }



}

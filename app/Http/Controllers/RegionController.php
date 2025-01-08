<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::check()){
            $region = Region::with(['country', 'division'])->get();
            return response()->json($region);
        }
        else{
            return response()->json(['message'=>'Unauthenticated'],400);
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
        // dd('rigion');

        $validation = Validator::make($request->all(),[
            'name' => 'required|string',
            'country_id'=>'required|integer',
            'division' =>'required|integer',
        ]);
        if($validation->fails()){
            return redirect()->back()->withErrors($validation->errors());
        }
        $rigions = Region::create([
            'name' => $request->name,
            'country_id' => $request->country_id,
            'division_id' => $request->division,
        ]);

        return response()->json(['Rigions'=>$rigions]);
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

            $region = Region::find($id);


            $validation = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'country_id' => 'required|integer|exists:countries,id',
                'division_id' => 'required|integer|exists:divisions,id',
            ]);


            if ($validation->fails()) {
                return response()->json(['errors' => $validation->errors()], 422);
            }


            if ($region) {

                $region->update([
                    'name' => $request->name,
                    'country_id' => $request->country_id,
                    'division_id' => $request->division_id,
                ]);

                return response()->json(['message' => 'Region updated successfully'], 200);

            } else {

                return response()->json(['message' => 'Region not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // dd('delete');
        if(Auth::check()){
            $region = Region::find($id);
          if($region){
            $region->delete();
            return response()->json(['message'=>'Region deleted successfully']);
          }
         else{
            return response()->json(['message'=>'Region not found'],404);
         }

        }
        else{
            return response()->json(['message'=>'Unauthenticated'],400);
        }

    }
}

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
            $data = Divisionoffice::latest()->with('division','managerName','district','project','country')->paginate(10);
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
            'office_name' => 'required|string|max:255|unique:divisionoffices,office_name',
            'project_id' => 'required|integer|exists:projects,id',
            'manager_id' => 'required|integer',
            'opening_date' => 'required|date_format:d/m/Y',
            'country_id' => 'required|integer|exists:countries,id',
            'division_id' => 'required|integer|exists:divisions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'upazila' => 'required|string',
            'union' => 'required|string',
            'manager_phone' => 'required|string',
            'address' => 'required|string',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $formattedopenDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->opening_date)->format('Y-m-d');
        $data = $request->all();
        $data['opening_date'] = $formattedopenDate;

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
    public function update(Request $request, $division_id)
    {
        // Validate the incoming request data

        if(Auth::check()){
            $validator = Validator::make($request->all(), [
            'office_name' => 'required|string',
            'project_id' => 'required|integer|exists:projects,id',
            'manager_id' => 'required|integer',
            'opening_date' => 'required|date',
            'country_id' => 'required|integer|exists:countries,id',
            'division_id' => 'required|integer|exists:divisions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'upazila' => 'required|string',
            'union' => 'required|string',
            'manager_phone' => 'required|string',
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
        $divisionOffice = Divisionoffice::find($division_id);


        $data = $divisionOffice->update($request->all());

        // Return the updated division office data
        return response()->json([
            'message' => 'Data updated successfully!!',
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
     * Remove the specified resource from storage.
     */
    public function destroy($divisionoffice)
    {
        if(Auth::check()){
            $divisionoffice = Divisionoffice::find($divisionoffice);
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

    public function searchDevisions(Request $request){
        if(Auth::check()){
            $search = $request->input('search');

            $divisionOfiice = Divisionoffice::with(['managerName'])
                            ->where('office_name', 'like', '%'.$search.'%')
                            ->orWhere('manager_phone', 'like', '%'.$search.'%')
                            ->get();

            return response()->json([
               'message' => 'Data found successfully',
                'data' => $divisionOfiice,
            ]);
        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
            ], 401);
        }

    }

    public function searchManagerName($employee_Id)
    {
        if(Auth::check()){
            $manager_details  = Divisionoffice::where('manager_Id', $employee_Id)->with('managerName')->get();

            return response()->json([
               'message' => 'Data found successfully',
                'data' => $manager_details,
            ]);
        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
            ], 401);
        }
    }

    public function divisionList(){
        if(Auth::check()){

            $manager_id = request('manager_id');
            $opening_date = request('opening_date');
            $status = request('status');
            $country_id = request('country_id');
            $division_id = request('division_id');
            $district_id = request('district_id');
            $upozila = request('upozila');




            $data = Divisionoffice::where(function ($query) use ($manager_id, $opening_date, $status, $country_id,$division_id,$upozila) {
                if (!empty($manager_id)) {
                    $query->Where('manager_id',  $manager_id );
                }
                if (!empty($country_id)) {
                    $query->Where('country_id',  $country_id );
                }
                if (!empty($division_id)) {
                    $query->Where('division_id',  $division_id );
                }
                if (!empty($upozila)) {
                    $query->Where('upazila',  'LIKE', '%' . $upozila . '%' );
                }

                if (!empty($opening_date)) {
                    $query->Where('opening_date', 'LIKE', '%' . $opening_date . '%' );
                }
                if (!empty($status)) {
                    $query->Where('status',  $status );
                }


                 })
                ->with(['managerName'])
                ->latest()
                ->paginate(10);


                return response()->json([
                    'message' => 'Data retrieved successfully',
                    'status' => true,
                    'data' => $data,
                ], 200);

        }
        else{
            return response()->json([

                'error'=> 'Unathorized',

            ]);
        }


    }




    public function showDivisionOffice($divisionOffice_id){
        if(Auth::check()){

            $division_office = Divisionoffice::where('id', $divisionOffice_id)->first();

            return response()->json([
               'message' => 'Data retrieved successfully',
                'data' => $division_office,
            ]);
        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
            ], 401);
        }

    }


}

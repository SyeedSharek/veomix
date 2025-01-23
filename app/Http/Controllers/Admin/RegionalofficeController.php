<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Regionaloffice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RegionalofficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $data = Regionaloffice::with(['divisionOffice', 'divisionOffice.employee', 'country', 'district', 'region'])->latest()->paginate(10);

            return response()->json([
                'message' => 'Data get successfully',
                'data' => $data,
            ]);
        } else {
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
        $validator = Validator::make($request->all(), [
            'regionalOfficeName' => 'required|string',
            'divisionoffice_id' => 'required|integer|exists:divisionoffices,id',
            'opening_date' => 'required|date_format:d/m/Y',
            'country_id' => 'required|integer|exists:countries,id',
            'district_id' => 'required|integer|exists:districts,id',
            'regional_id' => 'required|integer|exists:regions,id',
            'upozila' => 'required|string',
            'union' => 'required|string',
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


    public function update(Request $request, $riginalOffice_id)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'regionalOfficeName' => 'required|string|unique:regionaloffices,name',
            'divisionoffice_id' => 'required|integer|exists:divisionoffices,id',
            'opening_date' => 'required|date_format:d/m/Y',
            'country_id' => 'required|integer|exists:countries,id',
            'district_id' => 'required|integer|exists:districts,id',
            'regional_id' => 'required|integer|exists:regions,id',
            'upozila' => 'required|string',
            'union' => 'required|string',
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

        // Find the regional office
        $rigionalOffice = Regionaloffice::find($riginalOffice_id);

        $formattedopenDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->opening_date)->format('Y-m-d');

        $data = $request->all();
        $data['opening_date'] = $formattedopenDate;

        $rigionalOffice->update($data);


        return response()->json([
            'message' => 'Data updated successfully!!',
            'data' => $rigionalOffice,
        ]);
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy($rigionalOffice_id)
    {
        if (Auth::check()) {
            $rigionalOffice = Regionaloffice::find($rigionalOffice_id);
            $rigionalOffice->delete();
        } else {
            return response()->json([
                'errors' => 'Unauthorized',
            ]);
        }
    }


    public function searchRigionalOffice()
    {
        if (Auth::check()) {
            $search = request('search');
            $data = Regionaloffice::where('regionalOfficeName', 'LIKE', '%' . $search . '%')
                ->orWhere('opening_date', 'LIKE', '%' . $search . '%')
                ->with('divisionOffice', 'country', 'district', 'region')
                ->latest()
                ->paginate(10);

            return response()->json([
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'errors' => 'Unauthorized',
            ], 400);
        }
    }



    public function divisionWishSearch($division_id)
    {
        if (Auth::check()) {
            $manager_details  = Regionaloffice::where('divisionoffice_id', $division_id)->with('divisionOffice', 'country', 'district')->paginate(10);

            return response()->json([
                'message' => 'Data found successfully',
                'data' => $manager_details,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
    }






    public function showEmployeeName()
    {
        if (Auth::check()) {

            $data = Regionaloffice::where('divisionoffice_id',)->with('employees')->get();

            return response()->json([
                'message' => 'Data found successfully',
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
    }







    public function rigionalList()
    {
        if (Auth::check()) {

            $regional_id = request('regional_id');
            $employee_id = request('employee_id');
            $opening_date = request('opening_date');
            $status = request('status');
            $country_id = request('country_id');
            $district_id = request('district_id');
            $upozila = request('upozila');

            $data = Regionaloffice::where(function ($query) use ($regional_id, $employee_id, $opening_date, $status, $country_id, $district_id, $upozila) {
                if (!empty($regional_id)) {
                    $query->where('id', $regional_id);
                }
                if (!empty($employee_id)) {
                    $query->whereHas('divisionOffice.employee', function ($subQuery) use ($employee_id) {
                        $subQuery->where('id', $employee_id);
                    });
                }
                if (!empty($country_id)) {
                    $query->where('country_id', $country_id);
                }
                if (!empty($district_id)) {
                    $query->where('district_id', $district_id);
                }
                if (!empty($upozila)) {
                    $query->where('upozila', 'LIKE', '%' . $upozila . '%');
                }
                if (!empty($opening_date)) {
                    $query->where('opening_date', 'LIKE', '%' . $opening_date . '%');
                }
                if (!empty($status)) {
                    $query->where('status', $status);
                }
            })
            ->with(['divisionOffice.employee'])
            ->latest()
            ->paginate(10);

            return response()->json([
                'message' => 'Data retrieved successfully',
                'status' => true,
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ]);
        }
    }


    public function rigionalOfficeShow(){
        if(Auth::check()){
            $data = Regionaloffice::all();
            return response()->json([
                'data' => $data,
            ]);

        }
        else{
            return response()->json([
                'error' => 'Unauthorized',
            ]);
        }
    }











}

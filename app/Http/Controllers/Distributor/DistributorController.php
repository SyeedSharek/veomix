<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DistributorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::check()){
            $data = Distributor::with(['distributorGrade'])->latest()->paginate(10);
            return response()->json([
                'message' => 'Data get successfully',
                'data' => $data,
            ]);

        }
        else{

            return response()->json([
                'errors' => 'Unauthorized',
            ]);

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
            $validator = Validator::make($request->all(),[
                'distributorName' => 'required|string',
                'proprietorName' => 'required|string',
                'phoneNumber' => 'required|string',
                'contactPersonName' => 'required|string',
                'openDate' => 'required|date_format:d/m/Y',
                'email'=>'required|email',
                'webAddress'=> 'required|string|nullable',
                'distributorGrade' => 'required|integer',
                'distributorAddress' => 'required|string',

            ]);

            if($validator->fails()){
                return response()->json([
                    'errors' => $validator->errors(),
                ]);
            }
            $formattedOpenDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->openDate)->format('Y-m-d');
            $distributId =  random_int(10000, 99999);
            $data = $request->all();
            $data['openDate'] = $formattedOpenDate;
            $data['distributorId'] = $distributId;

            $distributor = Distributor::create($data);

            return response()->json([
               'message' => 'Data stored successfully!!',
                'data' => $distributor,
            ]);


        }
        else{
            return response()->json([
                'errors' => 'Unauthorized',
            ]);
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
    public function update(Request $request,  $id)
    {
        if(Auth::check()){
            $validator = Validator::make($request->all(),[
                'distributorName' => 'required|string',
                'proprietorName' => 'required|string',
                'phoneNumber' => 'required|string',
                'contactPersonName' => 'required|string',
                'openDate' => 'required|date_format:d/m/Y',
                'email'=>'required|email',
                'webAddress'=> 'required|string|nullable',
                'distributorGrade' => 'required|integer',
                'distributorAddress' => 'required|string',

            ]);

            if($validator->fails()){
                return response()->json([
                    'errors' => $validator->errors(),
                ]);
            }

            $formattedOpenDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->openDate)->format('Y-m-d');
            $data = $request->all();
            $data['openDate'] = $formattedOpenDate;
            $distributor = Distributor::find($id);
            if($distributor){
                $distributor->update($data);
                return response()->json([
                   'message' => 'Data updated successfully!!',
                    'data' => $distributor,
                ]);
            }

        }
        else{
            return response()->json([
                'errors' => 'Unauthorized',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id)
    {
        if(Auth::check()){
            $distributor = Distributor::find($id);
            if($distributor){
                $distributor->delete();
                return response()->json([
                   'message' => 'Data deleted successfully!!',
                ]);
            }
            else{
                return response()->json([
                    'errors' => 'Distributor not found!!',
                ]);
            }

        }
        else{
            return response()->json([
                'errors' => 'Unauthorized',
            ]);
        }
    }



    public function distributeWiseSearch(){
        if(Auth::check()){
            $distributorGrade = request('distributorGrade');

            $distributors = Distributor::with(['distributorGrade'])
                            ->where('distributorGrade', $distributorGrade)
                            ->latest()
                            ->paginate(10);

            return response()->json([
                'data' => $distributors,
            ]);

        }
        else{
            return response()->json([
               'message' => 'Unauthorized Access',
            ], 401);
        }
    }


    public function distributorSearch(){
        if(Auth::check()){
            $search = request('search');

            $distributors = Distributor::with(['distributorGrade'])
                            ->where('distributorName', 'like', '%'.$search.'%')
                            ->orWhere('proprietorName', 'like', '%'.$search.'%')
                            ->orWhere('phoneNumber', 'like', '%'.$search.'%')
                            ->orWhere('contactPersonName', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhere('webAddress', 'like', '%'.$search.'%')
                            ->orWhere('distributorAddress', 'like', '%'.$search.'%')
                            ->latest()
                            ->paginate(10);

            return response()->json([
                'data' => $distributors,
            ]);

        }
        else{
            return response()->json([
               'message' => 'Unauthorized Access',
            ], 401);
        }
    }



    public function distributorList(){

        if(Auth::check()){
            $distributorName = request('distributorName');
            $distributorId = request('distributorId');
            $phoneNumber = request('phoneNumber');
            $distributorGrade = request('distributorGrade');



            $data = Distributor::where(function ($query) use ($distributorName, $distributorId, $phoneNumber, $distributorGrade) {
                if (!empty($distributorName)) {
                    $query->Where('distributorName', 'LIKE', '%' . $distributorName . '%');
                }
                if (!empty($distributorId)) {
                    $query->Where('distributorId', 'LIKE', '%' . $distributorId . '%' );
                }
                if (!empty($phoneNumber)) {
                    $query->Where('phoneNumber', 'LIKE', '%' . $phoneNumber . '%');
                }
                if (!empty($distributorGrade)) {
                    $query->Where('distributorGrade',  $distributorGrade );
                }


                 })
                ->with(['distributorGrade'])
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






}

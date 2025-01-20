<?php

namespace App\Http\Controllers;

use App\Models\WholeSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WholeSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        if (Auth::check()) {
            $wholeSales = WholeSale::with(['clientGrade'])->latest()->paginate(10);
            return response()->json([
                'message' => 'Data get successfully',
                'data' => $wholeSales,
            ]);
        } else {
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
        if (Auth::check()) {


            $validator = Validator::make($request->all(), [
                'clientName' => 'required|string',
                'proprietorName' => 'required|string',
                'contactPersonName' => 'required|string',
                'phoneNumber' => 'required|string',
                'openDate' => 'required|date_format:d/m/Y',
                'email' => 'required|email',
                'webAddress' => 'required|string',
                'clientGrade_Id' => 'required|integer',
                'clientAddress' => 'required|string',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ]);
            }
            $clientId = random_int(10000, 99999);
            // dd($clientId);

            $formattedopenDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->openDate)->format('Y-m-d');
            $data = $request->all();
            $data['openDate'] = $formattedopenDate;
            $data['clientId'] = $clientId;

            $wholeSale = WholeSale::create($data);
            return response()->json([
                'message' => 'Data stored successfully!!',
                'data' => $wholeSale,
            ]);
        } else {
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
        if (Auth::check()) {


                $validator = Validator::make($request->all(), [
                    'clientName' => 'required|string',
                    'proprietorName' => 'required|string',
                    'contactPersonName' => 'required|string',
                    'phoneNumber' => 'required|string',
                    'openDate' => 'required|date_format:d/m/Y',
                    'email' => 'required|email',
                    'webAddress' => 'required|string',
                    'clientGrade_id' => 'required|integer',
                    'clientAddress' => 'required|string',

                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'errors' => $validator->errors(),
                    ]);
                }

                $data = $request->all();
                $formattedopenDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->openDate)->format('Y-m-d');
                $data['openDate'] = $formattedopenDate;

                $wholeSale = WholeSale::find($id);

                if ($wholeSale) {
                    $wholeSale->update($data);
                    return response()->json([
                       'message' => 'Data updated successfully',
                        'data' => $wholeSale,
                    ]);
                }

        } else {
            return response()->json([
                'errors' => 'Unauthorized',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (Auth::check()) {
            $wholeSale = WholeSale::find($id);
            if ($wholeSale) {
                $wholeSale->delete();
                return response()->json([
                    'message' => 'Data deleted successfully!!',
                ]);
            } else {
                return response()->json([
                    'message' => 'Data not found!!',
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized Access',
            ], 401);
        }
    }


    public function wholeSaleSearch(){
        if(Auth::check()){
            $search = request('search');

            $wholeSales = WholeSale::with(['clientGrade'])
                            ->where('clientName', 'like', '%'.$search.'%')
                            ->orWhere('proprietorName', 'like', '%'.$search.'%')
                            ->orWhere('contactPersonName', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhere('webAddress', 'like', '%'.$search.'%')
                            ->orWhere('clientAddress', 'like', '%'.$search.'%')
                            ->latest()
                            ->paginate(10);

            return response()->json([
                'data' => $wholeSales,
            ]);

        }
        else{
            return response()->json([
               'message' => 'Unauthorized Access',
            ], 401);
        }
    }

    public function clientWiseGradeSearch(){
        if(Auth::check()){
            $clientGradeId = request('clientGradeId');

            $wholeSales = WholeSale::with(['clientGrade'])
                            ->where('clientGrade_id', $clientGradeId)
                            ->latest()
                            ->get();

            return response()->json([
                'data' => $wholeSales,
            ]);

        }
        else{
            return response()->json([
               'message' => 'Unauthorized Access',
            ], 401);
        }
    }

    public function clientList(){

        if(Auth::check()){
            $clientName = request('clientName');
            $clientId = request('clientId');
            $mobile = request('phoneNumber');
            $clientGradeId = request('clientGradeId');



            $data = WholeSale::where(function ($query) use ($clientName, $clientId, $mobile, $clientGradeId) {
                if (!empty($clientName)) {
                    $query->Where('clientName', 'LIKE', '%' . $clientName . '%');
                }
                if (!empty($clientId)) {
                    $query->Where('clientId', 'LIKE', '%' . $clientId . '%' );
                }
                if (!empty($mobile)) {
                    $query->Where('phoneNumber', 'LIKE', '%' . $mobile . '%');
                }
                if (!empty($clientGradeId)) {
                    $query->Where('clientGrade_Id',  $clientGradeId );
                }


                 })
                ->with(['clientGrade'])
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

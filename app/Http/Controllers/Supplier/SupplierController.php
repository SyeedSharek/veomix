<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::check()){
            $data = Supplier::with(['supplierGrade','branch'])->latest()->get();
            return response()->json([
                'message' => 'Data get successfully',
                'data' => $data,
            ]);


        }
        else{
            return response()->json([

                'error'=> 'Unathorized',

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
        if (Auth::check()) {
            // Format the 'joingDate'

            $supplierId = random_int(100000, 999999);
            // dd($supplierId);

            $formattedJoingDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->openDate)->format('Y-m-d');

            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'supplierName' => 'required|string',
                'proprieTorModel' => 'required|string',
                'phoneNumber' => 'required|string',
                'contactPersonName' => 'required|string',
                'openDate' => 'required|date_format:d/m/Y',
                'email' => 'required|email',
                'webAddress' => 'string|nullable',
                'supplierGradeId' => 'required|integer',
                'supplierAddress' => 'required|string',
                'branchId' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 400);
            }


            $data = $request->all();
            $data['openDate'] = $formattedJoingDate;
            $data['supplierId'] = $supplierId;

            $supplier = Supplier::create($data);

            return response()->json([
                'message' => 'Supplier created successfully',
                'supplier' => $supplier,
            ], 201);
        } else {
            return response()->json([
                'errors' => 'Unauthorized',
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
    public function update(Request $request,  $id)
    {
        if(Auth::check()){
            // Format the 'joingDate'

            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'supplierName' => 'required|string',
                'proprieTorModel' => 'required|string',
                'phoneNumber' => 'required|string',
                'contactPersonName' => 'required|string',
                'openDate' => 'required|date_format:d/m/Y',
                'email' => 'required|email',
                'webAddress' => 'string|nullable',
                'supplierGradeId' => 'required|integer',
                'supplierAddress' => 'required|string',
                'branchId' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 400);
            }
            $formattedJoingDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->openDate)->format('Y-m-d');
            $data['openDate'] = $formattedJoingDate;
            $data = $request->all();

            $supplier = Supplier::find($id);
            if ($supplier) {
                $supplier->update($data);
                return response()->json([
                   'message' => 'Supplier updated successfully',
                   'supplier' => $supplier,
                ]);
            } else {
                return response()->json([
                   'message' => 'Supplier not found',
                ], 404);
            }

        }
        else{
            return response()->json([

                'error'=> 'Unathorized',

            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id)
    {
        if(Auth::check()){
            $supplier = Supplier::find($id);

            if ($supplier) {
                $supplier->delete();
                return response()->json([
                   'message' => 'Supplier deleted successfully',
                ]);
            } else {
                return response()->json([
                   'message' => 'Supplier not found',
                ], 404);
            }

        }
        else{
            return response()->json([

                'error'=> 'Unathorized',

            ]);
        }
    }


    public function searchSupplier(Request $request){
        if(Auth::check()){
            $search = $request->input('search');

            $data = Supplier::where('supplierName', 'LIKE', '%'.$search.'%')
                ->orWhere('proprieTorModel', 'LIKE', '%'.$search.'%')
                ->orWhere('phoneNumber', 'LIKE', '%'.$search.'%')
                ->orWhere('contactPersonName', 'LIKE', '%'.$search.'%')
                ->orWhere('email', 'LIKE', '%'.$search.'%')
                ->orWhere('webAddress', 'LIKE', '%'.$search.'%')
                ->orWhere('supplierAddress', 'LIKE', '%'.$search.'%')
                ->with(['supplierGrade','branch'])
                ->latest()
                ->get();

            return response()->json([
               'message' => 'Data get successfully',
                'data' => $data,
            ]);

        }
        else{
            return response()->json([

                'error'=> 'Unathorized',

            ]);
        }
    }


    public function brachWish_search(){
        if(Auth::check()){
            $brand_id = request('branchId');

            $data = Supplier::where('branchId', $brand_id)
                ->with(['supplierGrade','branch'])
                ->latest()
                ->get();

            return response()->json([
               'message' => 'Data get successfully',
                'data' => $data,
            ]);

        }
        else{
            return response()->json([

                'error'=> 'Unathorized',

            ]);
        }
    }


    public function supplierListSearch(){

        if(Auth::check()){
            $supplierGrade_id = request('supplierGradeId');
            $supplierName = request('supplierName');
            $supplierId = request('supplierId');
            $mobile = request('mobile');
            $contactPersonName = request('contactPersonName');
            $email = request('email');


            $data = Supplier::where(function ($query) use ($supplierGrade_id, $supplierName, $supplierId, $mobile, $contactPersonName,$email) {
                if (!empty($supplierName)) {
                    $query->Where('supplierName', 'LIKE', '%' . $supplierName . '%');
                }
                if (!empty($supplierId)) {
                    $query->Where('supplierId', 'LIKE', '%' . $supplierId . '%');
                }
                if (!empty($mobile)) {
                    $query->Where('phoneNumber', 'LIKE', '%' . $mobile . '%');
                }
                if (!empty($contactPersonName)) {
                    $query->Where('contactPersonName', 'LIKE', '%' . $contactPersonName . '%');
                }
                if (!empty($email)) {
                    $query->Where('email', 'LIKE', '%' . $email . '%');
                }

                if (!empty($supplierGrade_id)) {
                    $query->Where('supplierGradeId', $supplierGrade_id);
                }

            })
                ->with(['supplierGrade'])
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

<?php

namespace App\Http\Controllers\productWarranty;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductWarranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductWarrantyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::check()){
            $data = ProductWarranty::with(['category','brand','product'])->latest()->paginate(10);
            return response()->json([
               'message' => 'All Warranties',
               'data' => $data
            ],200);
        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
               'status' => 401,
            ],401);
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
                'category_id' => 'required|integer',
                'brand_id' => 'required|integer',
                'product_id' => 'required|integer',
                'model' => 'required|string',
                'productCoverage' => 'required|string',
                'warrantyLimitation' => 'required|string',
                'message' => 'required|string',

            ]);

            if($validator->fails()){
                return response()->json([
                    'errors' => $validator->errors(),
                ],400);
            }


            $data = $request->all();
            $warranty = ProductWarranty::create($data);
            return response()->json([
               'message' => 'Warranty Created Successfully',
                'data' => $warranty,
            ]);


        }
        else{
            return response()->json([
                'error'=> 'Unathorized',
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
    public function update(Request $request, string $id)
    {
        if(Auth::check()){
            $validator = Validator::make($request->all(),[
                'category_id' => 'required|integer',
                'brand_id' => 'required|integer',
                'product_id' => 'required|integer',
                'model' => 'required|string',
                'productCoverage' => 'required|string',
                'warrantyLimitation' => 'required|string',
                'message' => 'required|string',

            ]);

            if($validator->fails()){
                return response()->json([
                    'errors' => $validator->errors(),
                ],400);
            }

            $data = $request->all();
            $warranty = ProductWarranty::find($id);
            if($warranty){
                $warranty->update($data);
                return response()->json([
                   'message' => 'Warranty updated Successfully',
                    'data' => $warranty,
                ]);
            }

        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
               'status' => 401,
            ],401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if(Auth::check()){
            $warranty = ProductWarranty::find($id);
            if($warranty){
                $warranty->delete();
                return response()->json([
                   'message' => 'Warranty deleted Successfully',
                ],200);
            }
            else{
                return response()->json([
                   'message' => 'Warranty not found',
                ],404);
            }
        }
        else{
            return response()->json([
                'message' => 'Unauthenticated',
                'status' => 401,
             ],401);
        }



    }

    public function warrentySearch(){
        if(Auth::check()){
            $search = request('search');
            $warranty = ProductWarranty::where('model','LIKE','%'.$search.'%')
                ->orWhere('productCoverage','LIKE','%'.$search.'%')
                ->orWhere('warrantyLimitation','LIKE','%'.$search.'%')
                ->orWhere('message','LIKE','%'.$search.'%')
                ->with(['category','brand','product'])
                ->paginate(10);

            return response()->json([
               'message' => 'Search Result',
                'data' => $warranty,
            ]);
        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
               'status' => 401,
            ],401);
        }
    }

    public function productWishSearchh(){
        if(Auth::check()){
            $product_id = request('product_id');
            $product = ProductWarranty::where('product_id',$product_id)
                ->with(['brand','category','product'])
                ->paginate(10);

            return response()->json([
               'message' => 'Search Result',
                'data' => $product,
            ]);
        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
               'status' => 401,
            ],401);
        }

    }















    public function productDetails(){
        $products = Product::all();
        return response()->json([
           'message' => 'All Products',
            'data' => $products,
        ]);

    }

    public function categoryDetails(){

        $category = ProductCategory::all();
        return response()->json([
           'message' => 'All Category',
            'data' => $category,
        ]);


    }

    public function brandDetails(){

        $brand = ProductBrand::all();
        return response()->json([
           'message' => 'All Brand',
            'data' => $brand,
        ]);

    }





}

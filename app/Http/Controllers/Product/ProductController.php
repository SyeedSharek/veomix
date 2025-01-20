<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Nette\Utils\Random;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $data = Product::with(['productCategory', 'productBrand', 'productDiscountType'])->latest()->paginate(10);
            return response()->json([
                'message' => 'Data get successfully',
                'status' => true,
                'data' => $data
            ], 200);
        } else {
            return response()->json([
                'message' => 'Unauthorized Access',
                'status' => false
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

            $barcode = random_int(10000, 99999);
            // dd($barcode);


            $validator = Validator::make($request->all(), [
                'productName' => 'required|string',
                'productModel' => 'required|string',
                'category_id' => 'required|integer',
                'brand_id' => 'required|integer',
                'purchase_price' => 'required|string',
                'sales_price' => 'required|string',
                'wholeSale_price' => 'required|string',
                'tax_rate' => 'required|string',
                'loan_price' => 'required|string',
                'discountType_id' => 'required|integer',
                'discount_percentage' => 'required|string',
                'discountAmount' => 'required|string',
                'discountFormDate' => 'required|date_format:d/m/Y',
                'discountUpToDate' =>  'required|date_format:d/m/Y',
                'productType' => 'required',
                'productHighLight' => 'required',
                'productDescription' => 'required',

            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ], 400);
            }

            $request->merge([
                'discountFormDate' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->discountFormDate)->format('Y-m-d'),
                'discountUpToDate' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->discountUpToDate)->format('Y-m-d'),
                'barcode' => $barcode,
            ]);

            $product = Product::create($request->all());

            return response()->json([
                'message' => 'Data stored successfully!!',
                'data' => $product,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized',
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
        if (Auth::check()) {
            $validator = Validator::make($request->all(), [
                'productName' => 'required|string',
                'productModel' => 'required|string',
                'category_id' => 'required|integer',
                'brand_id' => 'required|integer',
                'purchase_price' => 'required|string',
                'sales_price' => 'required|string',
                'barcode' => 'required|string',
                'wholeSale_price' => 'required|string',
                'tax_rate' => 'required|string',
                'loan_price' => 'required|string',
                'discountType_id' => 'required|integer',
                'discount_percentage' => 'required|string',
                'discountAmount' => 'required|string',
                'discountFormDate' => 'required|date_format:d/m/Y',
                'discountUpToDate' =>  'required|date_format:d/m/Y',
                'productType' => 'required',
                'productHighLight' => 'required',
                'productDescription' => 'required',


            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ], 400);
            }

            $request->merge([
                'discountFormDate' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->discountFormDate)->format('Y-m-d'),
                'discountUpToDate' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->discountUpToDate)->format('Y-m-d'),
            ]);
            $product = Product::find($id);
            $product->update($request->all());
            return response()->json([
                'message' => 'Data updated successfully!!',
                'data' => $product,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (Auth::check()) {
            $product = Product::find($id);
            $product->delete();

            return response()->json([
                'message' => 'Data deleted successfully!!',
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
    }

    public function searchProduct()
    {
        if (Auth::check()) {
            $search = request('search');
            $data = Product::where('productName', 'LIKE', '%' . $search . '%')
                ->orWhere('productModel', 'LIKE', '%' . $search . '%')
                ->orWhere('purchase_price', 'LIKE', '%' . $search . '%')
                ->orWhere('productType', 'LIKE', '%' . $search . '%')
                ->with(['productCategory', 'productBrand', 'productDiscountType'])
                ->latest()->paginate(10);

            return response()->json([
                'message' => 'Data get successfully',
                'status' => true,
                'data' => $data
            ], 200);
        } else {
            return response()->json([
                'message' => 'Unauthorized Access',
                'status' => false
            ], 401);
        }
    }



    public function cat_brachWish_search()
    {
        if (Auth::check()) {
            $category = request('category_id');
            $brand = request('brand_id');

            $data = Product::where('category_id', $category)
                ->orWhere('brand_id', $brand)
                ->with(['productCategory', 'productBrand', 'productDiscountType'])
                ->latest()->paginate(10);

            return response()->json([
                'products' => $data,

            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized Access',
                'status' => false
            ], 401);
        }
    }


    // public function listProduct(){
    //     if(Auth::check()){
    //         $data = Product::with(['productCategory','productBrand','productDiscountType'])->latest()->paginate(10);
    //         return response()->json([
    //            'message' => 'Data get successfully',
    //            'status' => true,
    //            'data' => $data
    //         ], 200);

    //     }
    //     else{
    //         return response()->json([
    //            'message' => 'Unauthorized Access',
    //            'status' => false
    //         ], 401);
    //     }
    // }

    public function productSearchList()
    {
        if (Auth::check()) {
            $productName = request('productName');
            $productModel = request('productModel');
            $barcode = request('barcode');
            $category = request('category_id');
            $brand = request('brand_id');

            $data = Product::where(function ($query) use ($productName, $productModel, $barcode, $category, $brand) {
                if (!empty($productName)) {
                    $query->Where('productName', 'LIKE', '%' . $productName . '%');
                }
                if (!empty($productModel)) {
                    $query->Where('productModel', 'LIKE', '%' . $productModel . '%');
                }
                if (!empty($barcode)) {
                    $query->Where('barcode', 'LIKE', '%' . $barcode . '%');
                }
                if (!empty($category)) {
                    $query->Where('category_id', $category);
                }
                if (!empty($brand)) {
                    $query->Where('brand_id', $brand);
                }
            })
                ->with(['productCategory', 'productBrand'])
                ->latest()
                ->paginate(10);

            return response()->json([
                'message' => 'Data retrieved successfully',
                'status' => true,
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Unauthorized Access',
                'status' => false,
            ], 401);
        }
    }






}

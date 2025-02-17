<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\ProductBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductBrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $data = ProductBrand::latest()->paginate(10);
            return response()->json([
                'message' => 'Data get successfully',
                'data' => $data
            ], 200);
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
        if (Auth::check()) {
            $validator = Validator::make($request->all(), [
                'brandName' => 'required|string|unique:product_brands,brandName,',
                'status' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

            $brand = ProductBrand::create($request->all());
            return response()->json([
                'message' => 'Brand created successfully',
                'data' => $brand
            ], 201);
        } else {
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
    public function update(Request $request, $id)
    {
        if (Auth::check()) {
            $brand = ProductBrand::find($id);

            if ($brand) {
                $validator = Validator::make($request->all(), [
                    'brandName' => 'required|string|unique:product_brands,brandName,' . $brand->id,
                    'status' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'errors' => $validator->errors(),
                    ], 422);
                }

                $brand->update($request->all());
                return response()->json([
                    'message' => 'Brand updated successfully',
                    'data' => $brand
                ], 200);
            } else {
                return response()->json([
                    'errors' => 'Brand Not Found',
                ], 404);
            }
        } else {
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
        if (Auth::check()) {
            $brand = ProductBrand::find($id);
            if ($brand) {
                $brand->delete();
                return response()->json([
                    'message' => 'Brand Deleted Successfully',
                ], 200);
            } else {
                return response()->json([
                    'errors' => 'Brand Not Found',
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
    }


    public function searchProductBrand(Request $request)
    {
        if (Auth::check()) {

            $request->validate([
                'brandName' => 'required|string|min:1',
            ]);

            $name = $request->input('brandName');

            $brands = ProductBrand::where('brandName', 'LIKE', '%' . $name . '%')->get();
            if ($brands->isEmpty()) {
                return response()->json([
                    'message' => 'No brands found matching the search term.',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'message' => 'Search Results',
                'data' => $brands,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
    }



    public function statusSearchProductBrand(Request $request)
    {
        if (Auth::check()) {
            // Validate the input
            $request->validate([
                'brandName' => 'required|string|min:1',
                'status' => 'nullable|integer',
            ]);

            $name = $request->input('brandName');
            $status = $request->input('status');

            // Build the query
            $query = ProductBrand::where('brandName', 'LIKE', '%' . $name . '%');

            // Add status condition if provided
            if (!is_null($status)) {
                $query->where('status', $status);
            }

            $brands = $query->get();

            // Check if results are found
            if ($brands->isEmpty()) {
                return response()->json([
                    'message' => 'No brands found matching the search term.',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'message' => 'Search Results',
                'data' => $brands,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
    }



    public function statusChange($id)
    {
        if (Auth::check()) {
            // Find the brand by ID
            $brand = ProductBrand::find($id);

            if ($brand) {
                // Toggle the status: if 1, set to 0; if 0, set to 1
                $brand->status = $brand->status === 1 ? 0 : 1;
                $brand->save();

                return response()->json([
                    'message' => 'Status updated successfully',
                    'data' => $brand,
                ], 200);
            } else {
                return response()->json([
                    'errors' => 'Brand Not Found',
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
    }


    public function productBrandEyeView($productBrand_id){
        if(Auth::check()){
            $brand = ProductBrand::find($productBrand_id)->first();
            return response()->json([
                'data' => $brand
            ]);


        }
        else{
            return response()->json([
               'message' => 'Unauthenticated',
            ], 401);
        }

    }



}

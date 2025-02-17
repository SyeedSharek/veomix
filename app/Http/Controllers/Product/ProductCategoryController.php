<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::check()){
            $data = ProductCategory::latest()->paginate(10);
            return response()->json([
                'message' => 'All Categories',
                'data' => $data
            ],200);

        }else{
            return response()->json([
                'errors' =>'Unauthorized',

            ],400);
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
                'categoryName' => 'required|string|unique:product_categories,categoryName',
            ]);

            if($validator->fails()){
                return response()->json([
                    'errors' => $validator->errors(),
                ],400);
            }

            $category = ProductCategory::create($request->all());
            return response()->json([
               'message' => 'Category Created Successfully',
                'data' => $category
            ],201);

        }
        else{
            return response()->json([
                'errors' => 'Unauthorized',
            ],401);
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
            $validator = Validator::make($request->all(),[
                'categoryName' =>'required|string|unique:product_categories,categoryName,'.$id,
            ]);

            if($validator->fails()){
                return response()->json([
                    'errors' => $validator->errors(),
                ],400);
            }

            $category = ProductCategory::find($id);

            if($category){
                $category->update($request->all());
                return response()->json([
                   'message' => 'Category Updated Successfully',
                    'data' => $category
                ],200);
            } else{
                return response()->json([
                    'errors' => 'Category Not Found',
                ],404);
            }

        } else{
            return response()->json([
                'errors' => 'Unauthorized',
            ],401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if(Auth::check()){
            $category = ProductCategory::find($id);

            if($category){
                $category->delete();
                return response()->json([
                   'message' => 'Category Deleted Successfully',
                ],200);
            } else{
                return response()->json([
                    'errors' => 'Category Not Found',
                ],404);
            }

        }else{
            return response()->json([
                'errors' => 'Unauthorized',
            ],401);
        }
    }



    public function searchProductCategory(Request $request)
    {
        if (Auth::check()) {

            $request->validate([
                'search' => 'string|min:1',
            ]);

            $search = $request->input('search');

            $category = ProductCategory::where('categoryName', 'LIKE', '%' . $search . '%')->latest()->paginate(10);;

            if ($category->isEmpty()) {
                return response()->json([
                    'message' => 'No categories found matching the search term.',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'message' => 'Search Results',
                'data' => $category,
            ], 200);
        } else {
            return response()->json([
                'errors' => 'Unauthorized',
            ], 401);
        }
    }









    public function statusChange($id)
    {
        if (Auth::check()) {
            // Find the brand by ID
            $category = ProductCategory::find($id);

            if ($category) {
                // Toggle the status: if 1, set to 0; if 0, set to 1
                $category->status = $category->status === 1 ? 0 : 1;
                $category->save();

                return response()->json([
                    'message' => 'Status updated successfully',
                    'data' => $category,
                ], 200);
            } else {
                return response()->json([
                    'errors' => 'Category Not Found',
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
    }




    public function productCategoryEyeView($productCategory_id){
        if(Auth::check()){
            $productCategory = ProductCategory::where('id',$productCategory_id)
            ->first();

            return response()->json([
                'productCategory' => $productCategory

            ]);

        }


        else{
            return response()->json([
               'message' => 'Unauthenticated',
            ],401);
        }
    }











}

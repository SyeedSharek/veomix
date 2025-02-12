<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\CashDetail;
use App\Models\CashSale;
use App\Models\CustomerProductReturn;
use App\Models\CustomerSalesReturn as ModelsCustomerSalesReturn;
use App\Models\HireProductDetails;
use App\Models\HireProductSale;
use App\Models\ProductStockManagement;
use App\Models\WholeProductSale;
use App\Models\WholeProductSalesDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerSalesReturn extends Controller
{


    public function customer_product_return(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|integer',
            'product_id' => 'required|integer',
            'return_product_quantity' => 'required|integer',
            'purchase_date' => 'required|date_format:d/m/Y',
            'sales_type_id' => 'required|integer',
            'invoice_number' => 'required|string',
            'return_date' => 'required|date_format:d/m/Y',
            'return_reason' => 'required|string',
            'return_amount' => 'required|numeric',
        ]);



        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            DB::beginTransaction();

            $data = $request->all();
            $data['purchase_date'] = Carbon::createFromFormat('d/m/Y', $request->purchase_date)->format('Y-m-d');
            $data['return_date'] = Carbon::createFromFormat('d/m/Y', $request->return_date)->format('Y-m-d');

            $authUser = Auth::user()->name;



            $productStock = ProductStockManagement::where('product_id', $request->product_id)->first();
            if (!$productStock) {
                return response()->json(['message' => 'Stock record not found for this product'], 404);
            }
            $productStock->increment('total_product_quantity', $request->return_product_quantity);




            if ($request->sales_type_id == 1) {
                $cashSale = CashSale::where('invoice_number', $request->invoice_number)->first();
                if (!$cashSale) {
                    return response()->json(['message' => 'Invoice not found'], 404);
                }

                $productDetail = CashDetail::where('cash_id', $cashSale->id)
                    ->where('product_id', $request->product_id)
                    ->first();

                if (!$productDetail) {
                    return response()->json(['message' => 'Product details not found'], 404);
                }

                $product_subTotal = $productDetail->subtotal;
                $sales_quantity = $productDetail->product_quantity;
            } else if ($request->sales_type_id == 2) {


                $hireSalePorduct = HireProductSale::where('invoice_number', $request->invoice_number)->first();
                if (!$hireSalePorduct) {
                    return response()->json(['message' => 'Invoice not found'], 404);
                }

                $productDetail = HireProductDetails::where('hire_product_sales_id', $hireSalePorduct->id)
                    ->where('product_id', $request->product_id)
                    ->first();

                if (!$productDetail) {
                    return response()->json(['message' => 'Product details not found'], 404);
                }

                $product_subTotal = $productDetail->subtotal;
                $sales_quantity = $productDetail->product_quantity;
            } else if ($request->sales_type_id == 3) {


                $wholeSaleProduct = WholeProductSale::where('invoice_number', $request->invoice_number)->first();
                if (!$wholeSaleProduct) {
                    return response()->json(['message' => 'Invoice not found'], 404);
                }

                $productDetail = WholeProductSalesDetail::where('whole_product_sales_id', $wholeSaleProduct->id)
                    ->where('product_id', $request->product_id)
                    ->first();

                if (!$productDetail) {
                    return response()->json(['message' => 'Product details not found'], 404);
                }

                $product_subTotal = $productDetail->subtotal;
                $sales_quantity = $productDetail->product_quantity;
            }



            $existingReturn = CustomerProductReturn::where('member_id', $request->member_id)
                ->where('product_id', $request->product_id)
                ->where('sales_type_id', $request->sales_type_id)
                ->first();

            if ($existingReturn) {

                $existingReturn->increment('return_product_quantity', $request->return_product_quantity);
                $existingReturn->update([
                    'purchase_date'           => $data['purchase_date'],
                    'sales_type_id'           => $request->sales_type_id,
                    'invoice_number'          => $request->invoice_number,
                    'return_date'             => $data['return_date'],
                    'return_reason'           => $request->return_reason,
                    'return_amount'           => $request->return_amount,
                    'product_sub_total'       => $product_subTotal,
                    'sales_product_quantity' => $sales_quantity,
                    'entry_user_name'  => $authUser,

                ]);
                $salesReturn = $existingReturn;
            } else {
                $salesReturn = CustomerProductReturn::create([
                    'member_id'               => $request->member_id,
                    'product_id'              => $request->product_id,
                    'return_product_quantity' => $request->return_product_quantity,
                    'purchase_date'           => $data['purchase_date'],
                    'sales_type_id'           => $request->sales_type_id,
                    'invoice_number'          => $request->invoice_number,
                    'return_date'             => $data['return_date'],
                    'return_reason'           => $request->return_reason,
                    'return_amount'           => $request->return_amount,
                    'product_sub_total'       => $product_subTotal,
                    'sales_product_quantity'   => $sales_quantity,
                    'entry_user_name' => $authUser,

                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Product return recorded successfully',
                'data' => $salesReturn,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred while processing the request.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }



    public function return_product_details()
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }

        $salesReturn = CustomerProductReturn::with([

            'product' => function ($query) {
                $query->select('id', 'productName', 'productModel');
            },

        ])->orderBy('created_at', 'desc')
            ->paginate(10);


        return response()->json([
            'data' => $salesReturn,
        ], 200);
    }


    public function allSearch(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }

        $searchQuery = $request->input('search');
        $salesReturn = CustomerProductReturn::with([
            'product' => function ($query) use ($searchQuery) {
                $query->select('id', 'productName', 'productModel')
                    ->where('productName', 'like', '%' . $searchQuery . '%');
            },

        ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $salesReturn,
        ], 200);
    }


    public function memberIdWishProductSearch($member_id)
    {
        if (Auth::check()) {
            $salesReturn = CustomerProductReturn::with([
                'product' => function ($query) use ($member_id) {
                    $query->select('id', 'productName', 'productModel');
                }
            ])
                ->where('member_id', $member_id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'data' => $salesReturn,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }
    }

    public function allFilterSearch(Request $request)
    {
        if (Auth::check()) {
            $member_id = $request->input('member_id');
            $product_id = $request->input('product_id');
            $purchase_date = $request->input('purchase_date');
            $updated_at = $request->input('updated_at');
            $invoice = $request->input('invoice_number');
            $return_date = $request->input('return_date');

            $salesReturnQuery = CustomerProductReturn::with([
                'product' => function ($query) {
                    $query->select('id', 'productName', 'productModel');
                }
            ]);

            // Apply filters only if they are provided in the request
            if ($member_id) {
                $salesReturnQuery->where('member_id', $member_id);
            }

            if ($product_id) {
                $salesReturnQuery->where('product_id', $product_id);
            }

            if ($purchase_date) {
                $salesReturnQuery->where('purchase_date', 'LIKE', $purchase_date . '%');
            }

            if ($updated_at) {
                $salesReturnQuery->where('updated_at', 'LIKE', $updated_at . '%');
            }

            if ($invoice) {
                $salesReturnQuery->where('invoice_number', $invoice);
            }

            if ($return_date) {
                $salesReturnQuery->where('return_date', 'LIKE', $return_date . '%');
            }


            // Fetch data (either filtered or all if no filters are provided)
            $salesReturn = $salesReturnQuery->orderBy('created_at', 'desc')
                ->paginate(10);

            if ($salesReturn->isEmpty()) {
                return response()->json([
                    'message' => 'Data Not Found',
                ], 404);
            }

            return response()->json([
                'data' => $salesReturn,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }
    }


    public function returnProductList()
    {
        if (Auth::check()) {
            $salesReturn = CustomerProductReturn::with([
                'member' => function ($query) {
                    $query->select('id', 'memberName_english');
                }
            ])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => $salesReturn,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }
    }

    public function customerReturnProductUpdate(Request $request, $customer_product_return_id)
    {

        if (Auth::check()) {
            $authUser = Auth::user()->name;

            $validator = Validator::make($request->all(), [
                'member_id' => 'required|integer',
                'product_id' => 'required|integer',
                'return_product_quantity' => 'required|string',
                'purchase_date' => 'required|date_format:d/m/Y',
                'sales_type_id' => 'required|integer',
                'invoice_number' => 'required|string',
                'return_date' => 'required|date_format:d/m/Y',
                'return_reason' => 'required|string',
                'return_amount' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 400);
            }

            try {
                DB::beginTransaction();

                $data = $request->all();
                $data['purchase_date'] = Carbon::createFromFormat('d/m/Y', $request->purchase_date)->format('Y-m-d');
                $data['return_date'] = Carbon::createFromFormat('d/m/Y', $request->return_date)->format('Y-m-d');



                // Find existing return record
                $customerReturnProduct = CustomerProductReturn::find($customer_product_return_id);
                if (!$customerReturnProduct) {
                    return response()->json(['message' => 'Return record not found'], 404);
                }

                $product_quantity = $customerReturnProduct->return_product_quantity;


                $productStock = ProductStockManagement::where('product_id', $request->product_id)->first();
                if (!$productStock) {
                    return response()->json(['message' => 'Stock record not found for this product'], 404);
                }




                $difference = $request->return_product_quantity - $product_quantity;
                if ($difference > 0) {

                    $productStock->increment('total_product_quantity', $difference);
                } elseif ($difference < 0) {

                    $productStock->decrement('total_product_quantity', abs($difference));
                }





                // Fetch product details based on sales type
                $product_subTotal = 0;
                $sales_quantity = 0;

                if ($request->sales_type_id == 1) {
                    $cashSale = CashSale::where('invoice_number', $request->invoice_number)->first();
                    if (!$cashSale) {
                        return response()->json(['message' => 'Invoice not found'], 404);
                    }

                    $productDetail = CashDetail::where('cash_id', $cashSale->id)
                        ->where('product_id', $request->product_id)
                        ->first();

                    if ($productDetail) {
                        $product_subTotal = $productDetail->subtotal;
                        $sales_quantity = $productDetail->product_quantity;
                    }
                } elseif ($request->sales_type_id == 2) {
                    $hireSaleProduct = HireProductSale::where('invoice_number', $request->invoice_number)->first();
                    if (!$hireSaleProduct) {
                        return response()->json(['message' => 'Invoice not found'], 404);
                    }

                    $productDetail = HireProductDetails::where('hire_product_sales_id', $hireSaleProduct->id)
                        ->where('product_id', $request->product_id)
                        ->first();

                    if ($productDetail) {
                        $product_subTotal = $productDetail->subtotal;
                        $sales_quantity = $productDetail->product_quantity;
                    }
                } elseif ($request->sales_type_id == 3) {
                    $wholeSaleProduct = WholeProductSale::where('invoice_number', $request->invoice_number)->first();
                    if (!$wholeSaleProduct) {
                        return response()->json(['message' => 'Invoice not found'], 404);
                    }

                    $productDetail = WholeProductSalesDetail::where('whole_product_sales_id', $wholeSaleProduct->id)
                        ->where('product_id', $request->product_id)
                        ->first();

                    if ($productDetail) {
                        $product_subTotal = $productDetail->subtotal;
                        $sales_quantity = $productDetail->product_quantity;
                    }
                }




                // Update return record
                $customerReturnProduct->update([
                    'member_id'               => $request->member_id,
                    'product_id'              => $request->product_id,
                    'return_product_quantity' => $request->return_product_quantity,
                    'purchase_date'           => $data['purchase_date'],
                    'sales_type_id'           => $request->sales_type_id,
                    'invoice_number'          => $request->invoice_number,
                    'return_date'             => $data['return_date'],
                    'return_reason'           => $request->return_reason,
                    'return_amount'           => $request->return_amount,
                    'product_sub_total'       => $product_subTotal,
                    'sales_product_quantity'  => $sales_quantity,
                    'edit_user_name'         => $authUser,
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Product return updated successfully',
                    'data' => $customerReturnProduct,
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'error' => 'An error occurred while processing the request.',
                    'details' => $e->getMessage(),
                ], 500);
            }
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }
    }

    public function customerReturnProductDelete($customer_product_return_id)
    {
        if (Auth::check()) {
            $customerReturnProduct = CustomerProductReturn::find($customer_product_return_id);
            if (!$customerReturnProduct) {
                return response()->json(['message' => 'Return record not found'], 404);
            }
            $customerReturnProduct->delete();
            return response()->json([
                'message' => 'Product return deleted successfully',
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }
    }



    public function customerReturnMemberNameSearch(Request $request)
    {
        if (Auth::check()) {
            $searchQuery = $request->input('search');
            $salesReturn = CustomerProductReturn::with([
                'member' => function ($query) use ($searchQuery) {
                    $query->select('id', 'memberName_english')
                        ->where('memberName_english', 'like', '%' . $searchQuery . '%');
                },

            ])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'data' => $salesReturn,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }
    }


    public function customerReturnFilterSearch(Request $request)
    {
        if (Auth::check()) {

            $member_id = $request->input('member_id');
            $return_date = $request->input('return_date');
            $invoice_number = $request->input('invoice_number');
            $updated_at = $request->input('updated_at');


            $salesReturnQuery = CustomerProductReturn::with([
                'member' => function ($query) {
                    $query->select('id', 'memberName_english');
                }
            ]);

            // Apply filters only if they are provided in the request
            if ($member_id) {
                $salesReturnQuery->where('member_id', $member_id);
            }


            if ($updated_at) {
                $salesReturnQuery->where('updated_at', 'LIKE', $updated_at . '%');
            }

            if ($invoice_number) {
                $salesReturnQuery->where('invoice_number', $invoice_number);
            }

            if ($return_date) {
                $salesReturnQuery->where('return_date', 'LIKE', $return_date . '%');
            }



            $salesReturn = $salesReturnQuery->orderBy('created_at', 'desc')
                ->paginate(10);

            if ($salesReturn->isEmpty()) {
                return response()->json([
                    'message' => 'Data Not Found',
                ], 404);
            }

            return response()->json([
                'data' => $salesReturn,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }
    }


    public function customerReturnEyeViewData($customer_product_return_id){
        if(Auth::check()){
            $customerReturnProduct = CustomerProductReturn::with(['member','product'])->find($customer_product_return_id);

            if(!$customerReturnProduct){
                return response()->json([
                   'message' => 'Return record not found',
                ], 404);
            }

            return response()->json([
                'data' => $customerReturnProduct,
            ], 200);

        }
        else{
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }

    }





}

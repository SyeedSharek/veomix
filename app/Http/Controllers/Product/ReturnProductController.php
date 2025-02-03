<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\BillingDetail;
use App\Models\ReturnProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReturnProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */



    public function productReturn(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['message' => 'Unauthorized access'], 400);
            }


            $validator = Validator::make($request->all(), [
                'supplierId' => 'required|integer|exists:suppliers,id',
                'product_id' => 'required|integer|exists:products,id',
                'return_date' => 'required|date_format:d/m/Y',
                'return_reason' => 'required|string',
                'return_amount' => 'required|numeric',
                'return_quantity' => 'required|integer|min:1',
                'billing_id' => 'integer|exists:billings,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }


            $billing = Billing::where('supplier_id', $request->supplierId)->first();

            if (!$billing) {
                return response()->json(['message' => 'Billing record not found for this supplier'], 404);
            }


            $billingDetail = BillingDetail::where('billing_id', $billing->id)
                ->where('product_id', $request->product_id)
                ->first();

            if (!$billingDetail) {
                return response()->json(['message' => 'Product not found in this supplier’s billing'], 404);
            }


            if ($request->return_quantity > $billingDetail->quantity) {
                return response()->json(['message' => 'Return quantity exceeds available quantity'], 422);
            }


            DB::beginTransaction();

            // Calculate new total price after return
            $new_total_price = $billing->total_amount - $request->return_amount;


            $billing->update(['after_return_price' => $new_total_price]);


            $new_quantity = $billingDetail->quantity - $request->return_quantity;
            $billingDetail->update([
                'subtotal' => $billingDetail->subtotal - $request->return_amount,
                'quantity' => $new_quantity
            ]);


            $existingReturn = ReturnProduct::where('supplier_id', $request->supplierId)
                ->where('product_id', $request->product_id)
                ->first();

            if ($existingReturn) {

                $existingReturn->update([
                    'return_date' => Carbon::createFromFormat('d/m/Y', $request->return_date)->format('Y-m-d'),
                    'return_reason' => $request->return_reason,
                    'return_amount' => $request->return_amount,
                    'return_quantity' => $request->return_quantity,
                    'avilable_total_price' => $new_total_price,
                    'avilable_quantity' => $new_quantity
                ]);
            } else {

                ReturnProduct::create([
                    'supplier_id' => $request->supplierId,
                    'product_id' => $request->product_id,
                    'return_date' => Carbon::createFromFormat('d/m/Y', $request->return_date)->format('Y-m-d'),
                    'return_reason' => $request->return_reason,
                    'return_amount' => $request->return_amount,
                    'return_quantity' => $request->return_quantity,
                    'avilable_total_price' => $new_total_price,
                    'avilable_quantity' => $new_quantity
                ]);
            }


            DB::commit();

            return response()->json([
                'message' => 'Product return processed successfully',
                'updated_total_price' => $new_total_price,
                'updated_quantity' => $billingDetail->quantity
            ], 200);
        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while processing the return',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function showReturnDetails()
    {
        if (Auth::check()) {

            $return_product = ReturnProduct::with([
                'billing.billingDetails.product'
            ])->latest()->paginate(20);

            return response()->json([
                'message' => 'Return Product List',
                'data' => $return_product,
            ], 200);
        } else {

            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function searchReturnDetails(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized access'], 400);
        }

        $search = $request->input('search');


        $productPurchaseDetails = ReturnProduct::with(['billing.billingDetails.product'])
            ->whereHas('billing.billingDetails.product', function ($query) use ($search) {
                $query->where('productName', 'LIKE', '%' . $search . '%');
            })
            ->paginate(20);

        return response()->json([
            'message' => 'Search Results',
            'data' => $productPurchaseDetails
        ], 200);
    }


    /**
     * Display the specified resource.
     */
    public function searchReturnListIdWish(Request $request)
    {
        if(Auth::check()){
            $supplier_id = $request->input('supplier_id');
            $return_date = $request->input('return_date');
            $return_updated = $request->input('updated_at');

            $voucher_number = $request->input('voucher_number');

            $productPurchaseDetails = ReturnProduct::with(['billing.billingDetails.product'])

                ->when($return_date, function ($query) use ($return_date) {
                    $query->where('return_date', 'LIKE', '%' . $return_date . '%');
                })

                ->when($return_updated, function ($query) use ($return_updated) {
                    $query->where('updated_at', 'LIKE', '%' . $return_updated . '%');
                })

                ->whereHas('billing', function ($query) use ($supplier_id) {
                    $query->where('supplier_id', 'LIKE', '%' . $supplier_id . '%');
                })

                ->whereHas('billing.voucher_number', function ($query) use ($voucher_number) {
                    $query->where('supplier_id', 'LIKE', '%' . $voucher_number . '%');
                })


                ->paginate(20);

            return response()->json([
                'message' => 'Search Results',
                'data' => $productPurchaseDetails
            ], 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

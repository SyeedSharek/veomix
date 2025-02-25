<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\BillingDetail;
use App\Models\CreditAccount;
use App\Models\DamageProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DamageProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function DamageProductEntry(Request $request)
    {
        if (Auth::check()) {


            $auth_user = Auth::user()->name;

            $validator = Validator::make($request->all(), [
                'billing_id' => 'integer',
                'product_id' => 'integer|required|exists:products,id',
                'supplier_id' => 'integer|required|exists:suppliers,id',
                'damage_date' => 'required|date_format:d/m/Y',
                'damage_quantity' => 'required|numeric',
                'damage_amount' => 'required|numeric',
                'recieve_quantity' => 'required|numeric',
                'comment' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ], 400);
            }

            // Convert damage_date format
            $damage_date = \Carbon\Carbon::createFromFormat('d/m/Y', $request->damage_date)->format('Y-m-d');

            // Find the Billing record for the given supplier
            $billing = Billing::where('supplier_id', $request->supplier_id)->first();
            if (!$billing) {
                return response()->json([
                    'error' => 'No billing record found for the given supplier.'
                ], 404);
            }

            // Fetch the BillingDetail
            $billingDetail = BillingDetail::where('billing_id', $billing->id)
                ->where('product_id', $request->product_id)
                ->first();




            if (!$billingDetail) {
                return response()->json(['message' => 'Product not found in this supplier’s billing'], 404);
            }

            $total_quantity = $billingDetail->quantity;
            $total_product_sub_total = $billingDetail->subtotal;


            if ($request->return_quantity > $billingDetail->quantity) {
                return response()->json(['message' => 'Return quantity exceeds available quantity'], 422);
            }

            // Create Damage Product Entry
            $damage_Product = DamageProduct::create([
                'billing_id' => $billing->id, // Use the billing_id from the database
                'product_id' => $request->product_id,
                'supplier_id' => $request->supplier_id,
                'damage_date' => $damage_date,
                'damage_quantity' => $request->damage_quantity,
                'damage_amount' => $request->damage_amount,
                'recieve_quantity' => $request->recieve_quantity,
                'comment' => $request->comment,
                'total_quantity' => $total_quantity,
                'total_product_sub_total' => $total_product_sub_total,
                'entry_by' => $auth_user,
            ]);


            $credit = CreditAccount::create([
                'supplier_id' => $request->supplier_id,
                'damage_amount' => $request->damage_amount,
                'transaction_report_id' => 1,
            ]);



            return response()->json([
                'success' => true,
                'message' => 'Damage product entry created successfully',
                'data' => $damage_Product,
            ], 201);
        } else {

            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }
    }


    public function DamageProductSearch(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $search = $request->search;

        // Convert damage_date format if user enters d/m/Y
        try {
            $formattedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $search)->format('Y-m-d');
        } catch (\Exception $e) {
            $formattedDate = $search; // Use raw search if conversion fails
        }

        $damageProducts = DamageProduct::where('damage_date', 'LIKE', '%' . $formattedDate . '%')
            ->orWhere('damage_quantity', 'LIKE', '%' . $search . '%')
            ->orWhere('damage_amount', 'LIKE', '%' . $search . '%')
            ->orWhereHas('product', function ($query) use ($search) {
                $query->where('productName', 'LIKE', '%' . $search . '%');
            })
            ->orWhereHas('supplier', function ($query) use ($search) {
                $query->where('supplierName', 'LIKE', '%' . $search . '%');
            })
            ->with(['product', 'supplier', 'billing.billingDetails'])
            ->latest()
            ->paginate(10);


        return response()->json([
            'damage_products' => $damageProducts
        ], 200);
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
    public function damageProductUpdate(Request $request, $damage_id)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }

        $auth_user = Auth::user()->name;

        $validator = Validator::make($request->all(), [
            'billing_id' => 'integer',
            'product_id' => 'integer|required|exists:products,id',
            'supplier_id' => 'integer|required|exists:suppliers,id',
            'damage_date' => 'required|date_format:d/m/Y',
            'damage_quantity' => 'required|numeric',
            'damage_amount' => 'required|numeric',
            'recieve_quantity' => 'required|numeric',
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 400);
        }


        $damage_date = \Carbon\Carbon::createFromFormat('d/m/Y', $request->damage_date)->format('Y-m-d');


        // Find the Billing record for the given supplier
        $billing = Billing::where('supplier_id', $request->supplier_id)->first();
        if (!$billing) {
            return response()->json([
                'error' => 'No billing record found for the given supplier.'
            ], 404);
        }

        // Fetch the BillingDetail
        $billingDetail = BillingDetail::where('billing_id', $billing->id)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$billingDetail) {
            return response()->json(['message' => 'Product not found in this supplier’s billing'], 404);
        }

        $total_quantity = $billingDetail->quantity;
        $total_product_sub_total = $billingDetail->subtotal;

        // Ensure damage quantity does not exceed available quantity
        if ($request->damage_quantity > $billingDetail->quantity) {
            return response()->json(['message' => 'Damage quantity exceeds available quantity'], 422);
        }

        // Find and update DamageProduct record
        $damage_Product = DamageProduct::find($damage_id);
        if (!$damage_Product) {
            return response()->json(['message' => 'Damage product record not found'], 404);
        }

        $damage_Product->update([
            'billing_id' => $billing->id, // Use the billing_id from the database
            'product_id' => $request->product_id,
            'supplier_id' => $request->supplier_id,
            'damage_date' => $damage_date,
            'damage_quantity' => $request->damage_quantity,
            'damage_amount' => $request->damage_amount,
            'recieve_quantity' => $request->recieve_quantity,
            'comment' => $request->comment,
            'total_quantity' => $total_quantity,
            'total_product_sub_total' => $total_product_sub_total,
            'edited_by' => $auth_user,
        ]);

        return response()->json([
            'message' => 'Damage product updated successfully',
            'data' => $damage_Product
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function damageDelete($damage_id)
    {
        if(Auth::check()){
            $damage_product = DamageProduct::find($damage_id);
            if (!$damage_product) {
                return response()->json(['message' => 'Damage product record not found'], 404);
            }

            $damage_product->delete();

            return response()->json([
               'message' => 'Damage product deleted successfully',
            ], 200);

        }
        else{
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }
    }




    public function DamageProductShowDeatils()
    {
        if (Auth::check()) {
            $damage_products = DamageProduct::with('billing', 'product', 'supplier')->paginate(20);

            return response()->json([
                'data' => $damage_products,
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }
    }


    public function damageIdWishShow($damage_id)
    {
        if (Auth::check()) {
            $damage_product = DamageProduct::with('billing', 'product', 'supplier')->find($damage_id);

            return response()->json([
                'data' => $damage_product,
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }
    }







    public function supplierIdWishSearch($supplier_id)
    {
        if (Auth::check()) {
            $damage_products = DamageProduct::where('supplier_id', $supplier_id)->with('billing', 'product', 'supplier')->paginate(20);

            return response()->json([
                'data' => $damage_products,
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }
    }


    public function searchDamageListIdWish(Request $request)
    {

        if (Auth::check()) {
            $supplier_id = $request->input('supplier_id');
            $product_id = $request->input('product_id');
            $damage_date = $request->input('damage_date');
            $damage_quantity = $request->input('damage_quantity');



            $productDamage = DamageProduct::with(['billing', 'product', 'supplier'])

                ->when($damage_quantity, function ($query) use ($damage_quantity) {
                    $query->where('damage_quantity', 'LIKE', '%' . $damage_quantity . '%');
                })

                ->when($damage_date, function ($query) use ($damage_date) {
                    $query->where('damage_date', 'LIKE', '%' . $damage_date . '%');
                })

                ->when($supplier_id, function ($query) use ($supplier_id) {
                    $query->where('supplier_id', 'LIKE', '%' . $supplier_id . '%');
                })

                ->when($product_id, function ($query) use ($product_id) {
                    $query->where('product_id', 'LIKE', '%' . $product_id . '%');
                })


                ->paginate(20);

            return response()->json([
                'message' => 'Search Results',
                'data' => $productDamage,
            ], 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}

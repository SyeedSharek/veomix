<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\PurchaseBillingDetails;
use App\Models\PurchasePaymentDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PurchaseEntryController extends Controller
{


    public function productPurchase(Request $request)
    {
        if (Auth::check()) {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|array',
                'product_id.*' => 'integer',
                'supplier_id' => 'required|integer',
                'total_bill_amount' => 'required|string',
                'invoice_amount' => 'required|string',
                'product_quantity' => 'required|string',
                'after_discount_total_amount' => 'required|string',
                'customer_balance' => 'required|string',
                'customer_due_balance' => 'required|string',
                'payment_method_id' => 'required|string',
                'billing_id' => 'nullable|integer',
                'purchase_date' => 'required|date_format:d/m/Y',
                'product_warranty_date' => 'required|date_format:d/m/Y',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 400);
            }

            // Convert date formats
            $data = $request->all();
            $data['purchase_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->purchase_date)->format('Y-m-d');
            $data['product_warranty_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->product_warranty_date)->format('Y-m-d');



            // Save billing details
            $billingDetails = PurchaseBillingDetails::create([
                'supplier_id' => $data['supplier_id'],
                'product_id' => json_encode($data['product_id']),
                'total_bill_amount' => $data['total_bill_amount'],
                'invoice_amount' => $data['invoice_amount'],
                'product_quantity' => $data['product_quantity'],
                'after_discount_total_amount' => $data['after_discount_total_amount'],
                'customer_balance' => $data['customer_balance'],
                'customer_due_balance' => $data['customer_due_balance'],
            ]);

            // Save payment details
            $paymentDetails = PurchasePaymentDetails::create([
                'payment_method_id' => $data['payment_method_id'],
                'billing_id' => $billingDetails->id,
                'purchase_date' => $data['purchase_date'],
                'product_warranty_date' => $data['product_warranty_date'],
            ]);

            return response()->json([
                'message' => 'Purchase successful',
                'billing_details' => $billingDetails,
                'payment_details' => $paymentDetails,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Unauthorized'
            ], 400);
        }
    }



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
    public function store(Request $request)
    {
        //
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

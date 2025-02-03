<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\BillingDetail;
use App\Models\PaymentDetail;
use App\Models\PurchaseBillingDetails;
use App\Models\PurchasePaymentDetails;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseEntryController extends Controller
{


    // public function productPurchase(Request $request)
    // {
    //     if (Auth::check()) {

    //         $validator = Validator::make($request->all(), [
    //             'product_id' => 'required|array',
    //             'product_id.*' => 'integer',
    //             'supplier_id' => 'required|integer',
    //             'total_bill_amount' => 'required|string',
    //             'invoice_amount' => 'required|string',
    //             'product_quantity' => 'required|string',
    //             'after_discount_total_amount' => 'required|string',
    //             'customer_balance' => 'required|string',
    //             'customer_due_balance' => 'required|string',
    //             'payment_method_id' => 'required|string',
    //             'billing_id' => 'nullable|integer',
    //             'purchase_date' => 'required|date_format:d/m/Y',
    //             'product_warranty_date' => 'required|date_format:d/m/Y',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'errors' => $validator->errors(),
    //             ], 400);
    //         }

    //         // Convert date formats
    //         $data = $request->all();
    //         $data['purchase_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->purchase_date)->format('Y-m-d');
    //         $data['product_warranty_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->product_warranty_date)->format('Y-m-d');



    //         // Save billing details
    //         $billingDetails = PurchaseBillingDetails::create([
    //             'supplier_id' => $data['supplier_id'],
    //             'product_id' => json_encode($data['product_id']),
    //             'total_bill_amount' => $data['total_bill_amount'],
    //             'invoice_amount' => $data['invoice_amount'],
    //             'product_quantity' => $data['product_quantity'],
    //             'after_discount_total_amount' => $data['after_discount_total_amount'],
    //             'customer_balance' => $data['customer_balance'],
    //             'customer_due_balance' => $data['customer_due_balance'],
    //         ]);

    //         // Save payment details
    //         $paymentDetails = PurchasePaymentDetails::create([
    //             'payment_method_id' => $data['payment_method_id'],
    //             'billing_id' => $billingDetails->id,
    //             'purchase_date' => $data['purchase_date'],
    //             'product_warranty_date' => $data['product_warranty_date'],
    //         ]);

    //         return response()->json([
    //             'message' => 'Purchase successful',
    //             'billing_details' => $billingDetails,
    //             'payment_details' => $paymentDetails,
    //         ], 200);
    //     } else {
    //         return response()->json([
    //             'message' => 'Unauthorized'
    //         ], 400);
    //     }
    // }


    public function productPurchase(Request $request)
    {
        if (Auth::check()) {
            $authName = auth()->user()->name;




            // dd($request->all());
            $request->validate([
                'supplier_id' => 'required|integer',
                'voucher_number' => 'required|string',
                'products' => 'required|array',
                'products.*.product_id' => 'required|integer|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.price' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'customer_paid_amount' => 'required|numeric|min:0',
                'payment_method_id' => 'required|integer',
                'invoice_discount' => 'string',
                'purchase_date' => 'required|date_format:d/m/Y',
                'product_warrenty_date' => 'required|date_format:d/m/Y',
                'customer_due_balance' => 'nullable|string',
            ]);

            $request->merge([
                'purchase_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->purchase_date)->format('Y-m-d'),
                'product_warrenty_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->product_warrenty_date)->format('Y-m-d'),

            ]);

            DB::beginTransaction();
            try {
                // Step 1: Create Billing Entry
                $billing = Billing::create([
                    'supplier_id' => $request->supplier_id,
                    'voucher_number' => $request->voucher_number,
                    'total_amount' => $request->total_amount,
                    'purchase_date' => $request->purchase_date,
                    'product_warrenty_date' => $request->product_warrenty_date,
                    'customer_paid_amount' => $request->customer_paid_amount,
                    'entry_user_name' => $authName,


                ]);

                // Step 2: Insert Billing Details (Loop through products)
                foreach ($request->products as $product) {
                    BillingDetail::create([
                        'billing_id' => $billing->id,
                        'product_id' => $product['product_id'],
                        'quantity' => $product['quantity'],
                        'price' => $product['price'],
                        'subtotal' => $product['quantity'] * $product['price'],
                    ]);
                }

                // Step 3: Insert Payment Details

                $due_amount = $request->total_amount - $request->customer_paid_amount;

                PaymentDetail::create([
                    'billing_id' => $billing->id,
                    'customer_paid_amount' => $request->customer_paid_amount,
                    'customer_due_balance' => $due_amount,
                    'payment_method_id' => $request->payment_method_id,
                    'invoice_discount' => $request->invoice_discount,
                    'status' => ($request->customer_paid_amount >= $request->total_amount) ? '1' : '0',
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Bill created successfully',
                    'billing_id' => $billing->id
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Error creating bill',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized'
            ], 400);
        }
    }

    public function purchaseList()
    {
        if (Auth::check()) {
            $productPurchaseDetails = PaymentDetail::with(['billing', 'billing.billingDetails.product', 'billing.supllier'])->latest()->paginate(10);
            return response()->json([
                'product_purchase_details' => $productPurchaseDetails
            ], 200);
        } else {
            return response()->json([
                'message' => 'Unauthorized'
            ], 400);
        }
    }



    public function paymentIdWishShow($paymentId)
    {
        if (Auth::check()) {
            $paymentDetails = PaymentDetail::with(['billing', 'billing.billingDetails.product', 'billing.supllier'])->where('id', $paymentId)->first();
            return response()->json([
                'payment_details' => $paymentDetails
            ], 200);
        } else {
            return response()->json([
                'message' => 'Unauthorized'
            ], 400);
        }
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
    public function paymentUpdateWithBilling(Request $request, $paymentId)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $authName = auth()->user()->name;

        // Validate Request
        $request->validate([
            'supplier_id' => 'required|integer',
            'voucher_number' => 'required|string',
            'products' => 'required|array',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'customer_paid_amount' => 'required|numeric|min:0',
            'payment_method_id' => 'required|integer',
            'invoice_discount' => 'nullable|string',
            'purchase_date' => 'required|date_format:d/m/Y',
            'product_warrenty_date' => 'required|date_format:d/m/Y',
            'customer_due_balance' => 'nullable|string',
        ]);

        // Convert date format
        $request->merge([
            'purchase_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->purchase_date)->format('Y-m-d'),
            'product_warrenty_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->product_warrenty_date)->format('Y-m-d'),
        ]);

        DB::beginTransaction();
        try {
            // Step 1: Find existing payment record
            $paymentDetail = PaymentDetail::findOrFail($paymentId);


            $billing = $paymentDetail->billing;

            if (!$billing) {
                throw new \Exception("Billing record not found.");
            }

            // Step 3: Update Billing Record
            $billing->update([
                'supplier_id' => $request->supplier_id,
                'voucher_number' => $request->voucher_number,
                'total_amount' => $request->total_amount,
                'purchase_date' => $request->purchase_date,
                'product_warrenty_date' => $request->product_warrenty_date,
                'customer_paid_amount' => $request->customer_paid_amount,
                'update_by_name' => $authName,
            ]);

            // Step 4: Delete existing billing details
            $billing->billingDetails()->delete();

            // Step 5: Insert updated billing details (Loop through products)
            foreach ($request->products as $product) {
                BillingDetail::create([
                    'billing_id' => $billing->id,
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'price' => $product['price'],
                    'subtotal' => $product['quantity'] * $product['price'],
                ]);
            }

            // Step 6: Update Payment Details
            $due_amount = $request->total_amount - $request->customer_paid_amount;

            $paymentDetail->update([
                'customer_paid_amount' => $request->customer_paid_amount,
                'customer_due_balance' => $due_amount,
                'payment_method_id' => $request->payment_method_id,
                'invoice_discount' => $request->invoice_discount,
                'status' => ($request->customer_paid_amount >= $request->total_amount) ? '1' : '0',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payment and billing updated successfully',
                'billing_id' => $billing->id
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function paymentDeleteWithBilling($paymentId)
    {
        if (Auth::check()) {
            $paymentDetails = PaymentDetail::where('id', $paymentId)->first();

            if ($paymentDetails) {

                $billing = $paymentDetails->billing;
                $paymentDetails->delete();

                if ($billing) {

                    $billing->billingDetails()->delete();
                    $billing->delete();
                }

                return response()->json([
                    'message' => 'Payment and related billing deleted successfully'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Payment not found'
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized'
            ], 400);
        }
    }

    public function purchaseListSearch(Request $request)
    {
        if (Auth::check()) {
            $search = $request->input('search');
            $productPurchaseDetails = PaymentDetail::with(['billing', 'billing.billingDetails.product', 'billing.supplier'])

                ->orWhereHas('billing', function ($query) use ($search) {
                    $query->where('total_amount', 'LIKE', '%' . $search . '%');
                })
                ->orWhereHas('billing', function ($query) use ($search) {
                    $query->where('purchase_date', 'LIKE', '%' . $search . '%');
                })

                ->orWhereHas('billing.supplier', function ($query) use ($search) {
                    $query->where('supplierName', 'LIKE', '%' . $search . '%');
                })

                ->orWhereHas('billing.supplier', function ($query) use ($search) {
                    $query->where('phoneNumber', 'LIKE', '%' . $search . '%');
                })


                ->orWhereHas('billing', function ($query) use ($search) {
                    $query->where('product_warrenty_date', 'LIKE', '%' . $search . '%');
                })
                ->paginate(10);

            return response()->json([
                'product_purchase_details' => $productPurchaseDetails
            ], 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 400);
        }
    }


    public function purchaseIdWishListSearch(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $supplierName = $request->input('supplierName');
        $supplierPhone = $request->input('supplierPhone');
        $purchaseDate = $request->input('purchaseDate');
        $updatedAt = $request->input('updatedAt');
        $supplierBranchId = $request->input('supplier_branch_id');

        $productPurchaseDetails = PaymentDetail::with(['billing', 'billing.billingDetails.product', 'billing.supplier'])
            ->where(function ($query) use ($supplierName, $supplierPhone, $purchaseDate, $updatedAt, $supplierBranchId) {
                if (!empty($supplierName)) {
                    $query->whereHas('billing.supplier', function ($q) use ($supplierName) {
                        $q->where('supplierName', 'LIKE', '%' . $supplierName . '%');
                    });
                }
                if (!empty($supplierPhone)) {
                    $query->whereHas('billing.supplier', function ($q) use ($supplierPhone) {
                        $q->where('phoneNumber', 'LIKE', '%' . $supplierPhone . '%');
                    });
                }
                if (!empty($purchaseDate)) {
                    $query->whereHas('billing', function ($q) use ($purchaseDate) {
                        $q->where('purchase_date', 'LIKE', '%' . $purchaseDate . '%');
                    });
                }
                if (!empty($updatedAt)) {
                    $query->whereHas('billing', function ($q) use ($updatedAt) {
                        $q->where('updated_at', 'LIKE', '%' . $updatedAt . '%');
                    });
                }
                if (!empty($supplierBranchId)) {
                    $query->whereHas('billing.supplier', function ($q) use ($supplierBranchId) {
                        $q->where('branchId', $supplierBranchId);
                    });
                }
            })
            ->paginate(10);


        return response()->json([
            'product_purchase_details' => $productPurchaseDetails
        ], 200);
    }


    public function branchIdWishSearch($banchId){
        if (Auth::check()) {

            $productPurchaseDetails = PaymentDetail::with(['billing', 'billing.billingDetails.product', 'billing.supplier'])
            ->whereHas('billing.supplier', function ($query) use ($banchId) {
                $query->where('branchId', $banchId);
            })
            ->paginate(10);

        return response()->json([
            'product_purchase_details' => $productPurchaseDetails
        ], 200);

        }
        else{
            return response()->json(['message' => 'Unauthorized'], 400);
        }



    }




    public function supplyIdWishProductShow($supplyId)
{
    if (!Auth::check()) {
        return response()->json(['message' => 'Unauthorized'], 400);
    }

    // Check supplier existence first
    $supplierExists = Supplier::where('id', $supplyId)->exists();

    if (!$supplierExists) {
        return response()->json(['message' => 'Supplier not found'], 404);
    }

    // Query the product purchase details
    $productPurchaseDetails = PaymentDetail::with([
        'billing',
        'billing.billingDetails.product',
        'billing.supplier'
    ])
    ->whereHas('billing', function ($query) use ($supplyId) {
        $query->whereHas('supplier', function ($q) use ($supplyId) {
            $q->where('supplierId', $supplyId);
        })
        ->whereHas('billingDetails', function ($q) {
            $q->whereNotNull('product_id');
        });
    })
    ->get();

    // Debugging: Output query results before returning
    if ($productPurchaseDetails->isEmpty()) {
        return response()->json(['message' => 'No products found for this supplier'], 404);
    }

    return response()->json([
        'product_purchase_details' => $productPurchaseDetails
    ], 200);
}























}

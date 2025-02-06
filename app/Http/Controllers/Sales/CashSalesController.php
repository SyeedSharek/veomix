<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\BillingDetail;
use App\Models\CashDetail;
use App\Models\CashPayment;
use App\Models\CashSale;
use App\Models\HireLoanManagement;
use App\Models\HireProductDetails;
use App\Models\HireProductPayment;
use App\Models\HireProductSale;
use App\Models\InstallmentManage;
use App\Models\ProductStockManagement;
use App\Models\TaxAmount;
use App\Models\WholeProductSale;
use App\Models\WholeProductSalePayment;
use App\Models\WholeProductSalesDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Random;
use Illuminate\Support\Str;

class CashSalesController extends Controller
{
    public function cashSalesEntry(Request $request)
    {
        if (Auth::check()) {

            $authName = auth()->user()->name;


            // dd($request->all());
            $request->validate([
                'member_id' => 'required|integer',
                'products' => 'required|array',
                'products.*.product_id' => 'required|integer|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.price' => 'required|numeric|min:0',
                'products.*.discount_percentage' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'total_quantity' => 'required|numeric',

                'member_paid_amount' => 'required|numeric|min:0',
                'payment_type_id' => 'required|integer',
                'invoice_discount' => 'string',
                'invoice_date' => 'required|date_format:d/m/Y',
                'invoice_warranty' => 'required|date_format:d/m/Y',
                'billing_id' => 'integer|exists:billings,id',
                'supplierId' => 'required|integer|exists:suppliers,id',
            ]);

            $request->merge([
                'invoice_warranty' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->invoice_warranty)->format('Y-m-d'),
                'invoice_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->invoice_date)->format('Y-m-d'),

            ]);

            $tax_data = TaxAmount::first();
            $tax_amount =  $tax_data->tax_percentage;


            DB::beginTransaction();
            try {
                // Step 1: Create cash Entry
                $cashSales = CashSale::create([
                    'member_id' => $request->member_id,
                    'invoice_date' => $request->invoice_date,
                    'invoice_warranty' => $request->invoice_warranty,
                    'tax_percent' => $tax_amount,
                    'total_amount' => $request->total_amount,
                    'total_quantity' => $request->total_quantity,
                    'entry_name' => $authName,


                ]);


                // Step 2: Insert Cash Details (Loop through products)
                foreach ($request->products as $product) {

                    $product_id = $product['product_id'];
                    $product_quantity = $product['quantity'];

                    CashDetail::create([
                        'cash_id' => $cashSales->id,
                        'product_id' => $product['product_id'],
                        'product_quantity' => $product['quantity'],
                        'product_price' => $product['price'],
                        'product_discount_percentage' => $product['discount_percentage'],
                        'subtotal' => $product['quantity'] * $product['price'],

                    ]);

                    $billing = Billing::where('supplier_id', $request->supplierId)->first();



                    if (!$billing) {
                        return response()->json(['message' => 'Billing record not found for this supplier'], 404);
                    }


                    $billingDetail = BillingDetail::where('billing_id', $billing->id)
                        ->where('product_id', $product['product_id'])
                        ->first();

                    if ($billingDetail) {
                        $new_quantity = max(0, $billingDetail->quantity - $product['quantity']); // Prevent negative stock
                        $billingDetail->update(['avilable_stock_quantity' => $new_quantity]);
                    }


                    $productStock = ProductStockManagement::where('product_id', $product_id)->first();

                    if ($productStock) {
                        // Decrement quantity if the product exists
                        $productStock->decrement('total_product_quantity', $product_quantity);
                    } else {
                        return response()->json(['message' => 'Stock record not found for this product'], 404);
                    }
                }






                // Step 3: Insert Payment Details


                CashPayment::create([
                    'cash_id' => $cashSales->id,
                    'member_paid_amount' => $request->member_paid_amount,
                    'payment_type_id' => $request->payment_type_id,
                    'invoice_discount' => $request->invoice_discount,
                    'total_amount' => $request->total_amount,
                    'after_invoice_discount_total' => $request->total_amount - $request->invoice_discount,
                ]);






                DB::commit();

                return response()->json([
                    'message' => 'Bill created successfully',
                    'cashSales_id' => $cashSales->id
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



    public function wholeSalesEntry(Request $request)
    {
        if (Auth::check()) {

            $authName = auth()->user()->name;


            // dd($request->all());
            $request->validate([
                'whole_salier_member_id' => 'required|integer',
                'products' => 'required|array',
                'products.*.product_id' => 'required|integer|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.price' => 'required|string|min:0',
                'products.*.discount_percentage' => 'required|string|min:0',
                'total_amount' => 'required|string|min:0',
                'total_quantity' => 'required|string',
                'whole_sailer_paid_ammount' => 'required|string|min:0',
                'payment_type_id' => 'required|integer',
                'invoice_discount' => 'string',
                'invoice_date' => 'required|date_format:d/m/Y',
                'invoice_warranty' => 'required|date_format:d/m/Y',
                'billing_id' => 'integer|exists:billings,id',
                'supplierId' => 'required|integer|exists:suppliers,id',
            ]);

            $request->merge([
                'invoice_warranty' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->invoice_warranty)->format('Y-m-d'),
                'invoice_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->invoice_date)->format('Y-m-d'),

            ]);

            $tax_data = TaxAmount::first();
            $tax_amount =  $tax_data->tax_percentage;


            DB::beginTransaction();
            try {
                // Step 1: Create cash Entry
                $wholeProdcutSales = WholeProductSale::create([
                    'whole_salier_member_id' => $request->whole_salier_member_id,
                    'invoice_date' => $request->invoice_date,
                    'invoice_warranty' => $request->invoice_warranty,
                    'tax_amount' => $tax_amount,
                    'total_amount' => $request->total_amount,
                    'total_quantity' => $request->total_quantity,
                    'entry_name' => $authName,


                ]);


                // Step 2: Insert Cash Details (Loop through products)
                foreach ($request->products as $product) {

                    $product_id = $product['product_id'];
                    $product_quantity = $product['quantity'];
                    WholeProductSalesDetail::create([
                        'whole_product_sales_id' => $wholeProdcutSales->id,
                        'product_id' => $product['product_id'],
                        'product_quantity' => $product['quantity'],
                        'product_price' => $product['price'],
                        'product_discount_percentage' => $product['discount_percentage'],
                        'subtotal' => $product['quantity'] * $product['price'],

                    ]);


                    $billing = Billing::where('supplier_id', $request->supplierId)->first();



                    if (!$billing) {
                        return response()->json(['message' => 'Billing record not found for this supplier'], 404);
                    }


                    $billingDetail = BillingDetail::where('billing_id', $billing->id)
                        ->where('product_id', $product['product_id'])
                        ->first();

                    if ($billingDetail) {
                        $new_quantity = max(0, $billingDetail->quantity - $product['quantity']); // Prevent negative stock
                        $billingDetail->update(['avilable_stock_quantity' => $new_quantity]);
                    }


                    $productStock = ProductStockManagement::where('product_id', $product_id)->first();

                    if ($productStock) {
                        // Decrement quantity if the product exists
                        $productStock->decrement('total_product_quantity', $product_quantity);
                    } else {
                        return response()->json(['message' => 'Stock record not found for this product'], 404);
                    }
                }





                // Step 3: Insert Payment Details


                WholeProductSalePayment::create([
                    'whole_product_sales_id' => $wholeProdcutSales->id,
                    'whole_sailer_paid_ammount' => $request->whole_sailer_paid_ammount,
                    'payment_type_id' => $request->payment_type_id,
                    'invoice_discount' => $request->invoice_discount,
                    'total_amount' => $request->total_amount,
                    'after_invoice_discount_total' => $request->total_amount - $request->invoice_discount,
                ]);






                DB::commit();

                return response()->json([
                    'message' => 'Bill created successfully',
                    'wholeSale_id' => $wholeProdcutSales->id
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

    public function hireSalesEntry(Request $request)
    {
        if (Auth::check()) {

            $authName = auth()->user()->name;


            // dd($request->all());
            $request->validate([
                'member_id' => 'required|integer',
                'products' => 'required|array',
                'products.*.product_id' => 'required|integer|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.price' => 'required|string|min:0',
                'products.*.discount_percentage' => 'required|string|min:0',
                'total_amount' => 'required|string|min:0',
                'total_quantity' => 'required|string',


                'payment_type_id' => 'required|integer',
                'invoice_discount' => 'string',
                'invoice_date' => 'required|date_format:d/m/Y',
                'invoice_warranty' => 'required|date_format:d/m/Y',
                'billing_id' => 'integer|exists:billings,id',
                'supplierId' => 'required|integer|exists:suppliers,id',

                // installment Data
                'installment_type_id' => 'required|integer|exists:installment_types,id',
                'installment_number' => 'required|string',
                'paid_installment' => 'required|string',
                'installment_date' => 'required|date_format:d/m/Y',
                'installment_expired_date' => 'required|date_format:d/m/Y',
                'emi_amount' => 'required|string',
                "total_due_amount" => 'required|string',





            ]);

            $request->merge([
                'invoice_warranty' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->invoice_warranty)->format('Y-m-d'),
                'invoice_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->invoice_date)->format('Y-m-d'),

                'installment_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->installment_date)->format('Y-m-d'),
                'installment_expired_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->installment_expired_date)->format('Y-m-d'),

            ]);

            $tax_data = TaxAmount::first();
            $tax_amount =  $tax_data->tax_percentage;

            $invoice_number = mt_rand(1000, 9999);



            DB::beginTransaction();
            try {
                // Step 1: Create cash Entry
                $hireProductSale = HireProductSale::create([
                    'member_id' => $request->member_id,
                    'invoice_number' => $invoice_number,
                    'invoice_date' => $request->invoice_date,
                    'invoice_warranty' => $request->invoice_warranty,
                    'tax_amount' => $tax_amount,
                    'total_amount' => $request->total_amount,
                    'total_quantity' => $request->total_quantity,
                    'entry_name' => $authName,


                ]);


                // Step 2: Insert Cash Details (Loop through products)
                foreach ($request->products as $product) {

                    $product_id = $product['product_id'];
                    $product_quantity = $product['quantity'];

                    HireProductDetails::create([
                        'hire_product_sales_id' => $hireProductSale->id,
                        'product_id' => $product['product_id'],
                        'product_quantity' => $product['quantity'],
                        'product_price' => $product['price'],
                        'product_discount_percentage' => $product['discount_percentage'],
                        'subtotal' => $product['quantity'] * $product['price'],

                    ]);

                    $billing = Billing::where('supplier_id', $request->supplierId)->first();



                    if (!$billing) {
                        return response()->json(['message' => 'Billing record not found for this supplier'], 404);
                    }


                    $billingDetail = BillingDetail::where('billing_id', $billing->id)
                        ->where('product_id', $product['product_id'])
                        ->first();

                    if ($billingDetail) {
                        $new_quantity = max(0, $billingDetail->quantity - $product['quantity']); // Prevent negative stock
                        $billingDetail->update(['avilable_stock_quantity' => $new_quantity]);
                    }


                    $productStock = ProductStockManagement::where('product_id', $product_id)->first();

                    if ($productStock) {
                        // Decrement quantity if the product exists
                        $productStock->decrement('total_product_quantity', $product_quantity);
                    } else {
                        return response()->json(['message' => 'Stock record not found for this product'], 404);
                    }
                }





                // Step 3: Insert Payment Details


                HireProductPayment::create([
                    'hire_product_sales_id' => $hireProductSale->id,
                    'payment_type_id' => $request->payment_type_id,
                    'invoice_discount' => $request->invoice_discount,
                    'total_amount' => $request->total_amount,
                    'after_invoice_discount_total' => $request->total_amount - $request->invoice_discount,
                ]);



                HireLoanManagement::create([
                'installment_type_id' => $request->installment_type_id,
                'installment_number' => $request->installment_number,
                'paid_installment' => $request->paid_installment,
                'installment_date' => $request->installment_date,
                'installment_expired_date' => $request->installment_expired_date,
                'emi_amount' => $request->emi_amount,
                'member_id' => $request->member_id,
                'invoice_number' => $invoice_number,
                'total_due_amount' => $request->total_due_amount,

                ]);


                InstallmentManage::create([
                    'member_id' => $request->member_id,
                    'invoice_number'=>$invoice_number,
                    'total_amount' =>  $request->total_amount,
                    'paid_installment_loan' => $request->paid_installment,
                    'total_due_amount' => $request->total_due_amount,
                    'due_amount' => $request->total_due_amount,
                    'total_installment' => $request->installment_number,
                    'due_installment' =>$request->installment_number,
                    'installment_date' =>$request->installment_date,
                    'installment_expired_date' =>  $request->installment_expired_date,

                ]);








                DB::commit();

                return response()->json([
                    'message' => 'Bill created successfully',
                    'hire_sale_id' => $hireProductSale->id
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




}

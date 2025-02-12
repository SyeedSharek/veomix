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
                'supplierId' => 'integer|exists:suppliers,id',
                'sale_type_id' => 'required|integer',
            ]);

            $request->merge([
                'invoice_warranty' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->invoice_warranty)->format('Y-m-d'),
                'invoice_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->invoice_date)->format('Y-m-d'),

            ]);

            $tax_data = TaxAmount::first();
            $tax_amount =  $tax_data->tax_percentage;

            $invoice_number = mt_rand(1000, 9999);


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
                    'invoice_number' => $invoice_number,
                    'sale_type_id' => $request->sale_type_id,


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
                'sale_type_id' => 'required|integer',
            ]);

            $request->merge([
                'invoice_warranty' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->invoice_warranty)->format('Y-m-d'),
                'invoice_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->invoice_date)->format('Y-m-d'),

            ]);

            $tax_data = TaxAmount::first();
            $tax_amount =  $tax_data->tax_percentage;
            $invoice_number = mt_rand(1000, 9999);


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
                    'sale_type_id' => $request->sale_type_id,
                    'invoice_number' => $invoice_number,


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
                'sale_type_id' => 'required|integer',






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
                    'sale_type_id' => $request->sale_type_id,


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
                    'invoice_number' => $invoice_number,
                    'total_amount' =>  $request->total_amount,
                    'paid_installment_loan' => $request->paid_installment,
                    'total_due_amount' => $request->total_due_amount,
                    'due_amount' => $request->total_due_amount,
                    'total_installment' => $request->installment_number,
                    'due_installment' => $request->installment_number,
                    'installment_date' => $request->installment_date,
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




    public function showSalesInvoiceIndex()
    {
        if (Auth::check()) {

            $cashSales = CashSale::with(['cashDetails.product', 'cashPayments.paymentMethod'])
                ->latest()
                ->paginate(20);


            $hireSales = HireProductSale::with(['hireProductSaleDetail.product', 'hirePayments.paymentMethod'])
                ->latest()
                ->paginate(20);



            $wholeSales = WholeProductSale::with('wholeProductSalesDetail.product', 'wholePayment.paymentMethod')
                ->latest()
                ->paginate(20);



            return response()->json([
                'cashSales' => $cashSales,
                'hireSales' => $hireSales,
                'wholeSales' => $wholeSales,

            ], 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    // $query = CashSale::with('member', 'member.branchGroup','member.branchGroup.employee');

    public function invoiceIdWishSearch(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $sales_type_id = $request->sales_type_id;

        if ($sales_type_id == 1) {
            $query = CashSale::with([
                'member' => function ($query) {
                    $query->select('id', 'memberName_english', 'phoneNumber', 'present_address', 'banchGroup_id');
                },
                'member.branchGroup' => function ($query) {
                    $query->select('id', 'group_name');
                    // Select the actual columns from branchGroup
                }

            ])->where('sale_type_id', $sales_type_id);



            if ($request->has('invoice_date')) {
                $query->where('invoice_date', $request->invoice_date);
            }

            if ($request->has('updated_at')) {
                $query->whereDate('updated_at', $request->updated_at);
            }

            if ($request->has('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            // Filter by `banchGroup_id` inside the related `memeber` model
            if ($request->has('banchGroup_id')) {
                $query->whereHas('memeber', function ($memberQuery) use ($request) {
                    $memberQuery->where('banchGroup_id', $request->banchGroup_id);
                });
            }

            // Search by `employee_id` inside `branch_group`
            if ($request->has('employee_id')) {
                $query->whereHas('member.branchGroup', function ($branchGroupQuery) use ($request) {
                    $branchGroupQuery->where('employee_id', $request->employee_id);
                });
            }


            // Search by `employee_id` inside `branch_group and BranchManage Id inserd in branch group `
            if ($request->has('employee_id')) {
                $query->whereHas('member.branchGroup.employee', function ($branchGroupQuery) use ($request) {
                    $branchGroupQuery->where('branch_manage_id', $request->branch_manage_id);
                });
            }

            // Search by `employee_id` inside `branch_group`
            if ($request->has('employee_id')) {
                $query->whereHas('member.branchGroup.employee.managerName', function ($branchGroupQuery) use ($request) {
                    $branchGroupQuery->where('managerName', $request->managerName);
                });
            }


            if ($request->has('member_id')) {
                $query->where('member_id', $request->member_id);
            }



            if ($request->has('whole_member_id')) {
                $query->where('whole_member_id', $request->whole_member_id);
            }

            $cashSales = $query->paginate(20);

            return response()->json([
                'cashSales' => $cashSales
            ]);
        } elseif ($sales_type_id == 2) {
            $query = HireProductSale::with([
                'member' => function ($query) {
                    $query->select('id', 'memberName_english', 'phoneNumber', 'present_address', 'banchGroup_id');
                },
                'member.branchGroup' => function ($query) {
                    $query->select('id', 'group_name');
                    // Select the actual columns from branchGroup
                }

            ])->where('sale_type_id', $sales_type_id);



            if ($request->has('invoice_date')) {
                $query->where('invoice_date', $request->invoice_date);
            }

            if ($request->has('updated_at')) {
                $query->whereDate('updated_at', $request->updated_at);
            }

            if ($request->has('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            // Filter by `banchGroup_id` inside the related `memeber` model
            if ($request->has('banchGroup_id')) {
                $query->whereHas('memeber', function ($memberQuery) use ($request) {
                    $memberQuery->where('banchGroup_id', $request->banchGroup_id);
                });
            }

            // Search by `employee_id` inside `branch_group`
            if ($request->has('employee_id')) {
                $query->whereHas('member.branchGroup', function ($branchGroupQuery) use ($request) {
                    $branchGroupQuery->where('employee_id', $request->employee_id);
                });
            }


            // Search by `employee_id` inside `branch_group and BranchManage Id inserd in branch group `
            if ($request->has('employee_id')) {
                $query->whereHas('member.branchGroup.employee', function ($branchGroupQuery) use ($request) {
                    $branchGroupQuery->where('branch_manage_id', $request->branch_manage_id);
                });
            }

            // Search by `employee_id` inside `branch_group`
            if ($request->has('employee_id')) {
                $query->whereHas('member.branchGroup.employee.managerName', function ($branchGroupQuery) use ($request) {
                    $branchGroupQuery->where('managerName', $request->managerName);
                });
            }


            if ($request->has('member_id')) {
                $query->where('member_id', $request->member_id);
            }


            $hireSales = $query->paginate(20);

            return response()->json([
                'hireSales' => $hireSales
            ]);
        } elseif ($sales_type_id == 3) {
            $query = WholeProductSale::with([
                'wholeSalier' => function ($query) {
                    $query->select('id', 'clientName', 'phoneNumber', 'clientAddress');
                }

            ])->where('sale_type_id', $sales_type_id);



            if ($request->has('invoice_date')) {
                $query->where('invoice_date', $request->invoice_date);
            }

            if ($request->has('updated_at')) {
                $query->whereDate('updated_at', $request->updated_at);
            }




            if ($request->has('whole_member_id')) {
                $query->where('whole_member_id', $request->whole_member_id);
            }

            $wholeSales = $query->paginate(20);

            return response()->json([
                'wholeSales' => $wholeSales
            ]);
        } else {
            return response()->json([
                'message' => 'Invalid sales type id',
                'status' => 'error'
            ], 400);
        }
    }



    public function eyeViewSalesIdDetails($sales_type_id)
    {
        if (Auth::check()) {
            if ($sales_type_id == 1) {
                $cashSales = CashSale::with(['cashDetails.product', 'cashPayments.paymentMethod'])
                    ->where('sale_type_id', $sales_type_id)
                    ->first();


                return response()->json([
                    'cashSales' => $cashSales
                ]);
            } elseif ($sales_type_id == 2) {
                $hireSale = HireProductSale::with(['hireProductSaleDetail.product', 'hirePayments.paymentMethod'])
                    ->where('sale_type_id', $sales_type_id)
                    ->first();



                return response()->json([
                    'hireSale' => $hireSale
                ]);
            } elseif ($sales_type_id == 3) {

                $wholeSales = WholeProductSale::with('wholeProductSalesDetail.product', 'wholePayment.paymentMethod')
                    ->where('sale_type_id', $sales_type_id)
                    ->first();



                return response()->json([
                    'wholeSale' => $wholeSales
                ]);
            } else {
                return response()->json(['message' => 'Invalid Sale Type'], 400);
            }
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function deleteSalesInvoice($id, $sales_type_id)
    {


        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }



        if ($sales_type_id == 1) {
            $cashSale = CashSale::where('sale_type_id', $sales_type_id)->where('id', $id)->first();
            if (!$cashSale) {
                return response()->json(['message' => 'Cash Sale not found'], 404);
            }
            $cashSale->delete();
            return response()->json(['message' => 'Cash Sale deleted successfully'], 200);
        } elseif ($sales_type_id == 2) {
            $hireSale = HireProductSale::where('id', $id)->first();
            if (!$hireSale) {
                return response()->json(['message' => 'Hire Sale not found'], 404);
            }
            $hireSale->delete();
            return response()->json(['message' => 'Hire Sale deleted successfully'], 200);
        } elseif ($sales_type_id == 3) {
            $wholeSale = WholeProductSale::where('id', $id)->first();
            if (!$wholeSale) {
                return response()->json(['message' => 'Whole Sale not found'], 404);
            }
            $wholeSale->delete();
            return response()->json(['message' => 'Whole Sale deleted successfully'], 200);
        }

        return response()->json(['message' => 'Invalid Sale Type'], 400);
    }





    public function allSearchInvoiceList(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $search = $request->search;

        $query = HireProductSale::with([
            'member' => function ($query) {
                $query->select('id', 'memberName_english', 'phoneNumber', 'present_address', 'banchGroup_id');
            },
            'member.branchGroup' => function ($query) {
                $query->select('id', 'group_name');
            }
        ]);

        // Search by member name OR group name
        if ($request->has('search') && !empty($search)) {
            $query->where(function ($q) use ($search) {
                // Search in member name
                $q->whereHas('member', function ($memberQuery) use ($search) {
                    $memberQuery->where('memberName_english', 'LIKE', '%' . $search . '%');
                });

                // Search in group name
                $q->orWhereHas('member.branchGroup', function ($groupQuery) use ($search) {
                    $groupQuery->where('group_name', 'LIKE', '%' . $search . '%');
                });
            });
        }

        $hireSales = $query->paginate(20);

        return response()->json([
            'hireSales' => $hireSales
        ]);
    }


    public function showAllDetailsSalesType($sales_type)
    {
        if (Auth::check()) {

            if ($sales_type == 1) {
                $cashSales = CashSale::with([
                    'member:id,memberName_english,banchGroup_id',
                    'member.branchGroup:id,group_name,employee_id',


                    'cashDetails' => function ($query) {
                        $query->select('id', 'cash_id', 'product_id');
                    },
                    'cashDetails.product:id,productName,category_id,brand_id',
                    'cashDetails.product.productCategory:id,categoryName',
                    'cashDetails.product.ProductBrand:id,brandName',

                ])->get();

                return response()->json([
                    'cashSales' => $cashSales
                ]);
            }

            if ($sales_type == 2) {
                $hireSales = HireProductSale::with([
                    'member:id,memberName_english',
                    'hireProductSaleDetail' => function ($query) {
                        $query->select('id', 'hire_product_sales_id', 'product_id');
                    },
                    'hireProductSaleDetail.product:id,productName,category_id',
                    'hireProductSaleDetail.product.productCategory:id,categoryName'

                ])->get();


                return response()->json([
                    'hireSales' => $hireSales
                ]);
            }

            if ($sales_type == 3) {
                $wholeSales = WholeProductSale::with([
                    'wholeSalier:id,clientName',
                    'wholeProductSalesDetail' => function ($query) {
                        $query->select('id', 'whole_product_sales_id', 'product_id');
                    },
                    'wholeProductSalesDetail.product:id,productName,category_id',
                    'wholeProductSalesDetail.product.productCategory:id,categoryName'

                ])->get();


                return response()->json([
                    'wholeSales' => $wholeSales
                ]);
            }
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function invoice_numberWishProductShow($invoice_number)
    {
        if (Auth::check()) {
            // Retrieve the ID properly
            $cashSale = CashSale::where('invoice_number', $invoice_number)->first();

            if (!$cashSale) {
                return response()->json(['message' => 'Invoice not found'], 404);
            }

            // Now use $cashSale->id instead of the whole object
            $product_details = CashDetail::with('product')->where('cash_id', $cashSale->id)->get();

            return response()->json($product_details);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function dayWishSearchList(Request $request)
    {
        if (Auth::check()) {
            $sales_type_id = $request->input('sales_type_id');
            $purchase_date = $request->input('purchase_date');
            $updated_at = $request->input('updated_at');
            $branch_id = $request->input('branch_id');


            if ($sales_type_id == 1) {
                $cashSalesQuery = CashSale::with([
                    'member',
                    'member.branchGroup',
                    'member.branchGroup.employee',
                    'member.branchGroup.employee.branchName',
                    'cashDetails',
                    'cashPayments'

                ]);



                if ($purchase_date && $updated_at) {
                    // Filter sales records where invoice_date is between purchase_date and updated_at
                    $cashSalesQuery->whereBetween('invoice_date', [$purchase_date, $updated_at]);
                }

                if ($branch_id) {
                    $cashSalesQuery->whereHas('member.branchGroup.employee.branchName', function ($query) use ($branch_id) {
                        $query->where('id', $branch_id);
                    });
                }



                $cashSalesDetails = $cashSalesQuery->orderBy('created_at', 'desc')
                    ->paginate(10);

                if ($cashSalesDetails->isEmpty()) {
                    return response()->json([
                        'message' => 'Data Not Found',
                    ], 404);
                }

                return response()->json([
                    'cashSaleDetails' => $cashSalesDetails,
                ], 200);
            } elseif ($sales_type_id == 2) {

                $hireSalesQuery = HireProductSale::with([
                    'member',
                    'member.branchGroup',
                    'member.branchGroup.employee',
                    'member.branchGroup.employee.branchName',
                    'hireProductSaleDetail',
                    'hirePayments'

                ]);



                if ($purchase_date && $updated_at) {
                    // Filter sales records where invoice_date is between purchase_date and updated_at
                    $hireSalesQuery->whereBetween('invoice_date', [$purchase_date, $updated_at]);
                }

                if ($branch_id) {
                    $hireSalesQuery->whereHas('member.branchGroup.employee.branchName', function ($query) use ($branch_id) {
                        $query->where('id', $branch_id);
                    });
                }



                $hireSalesDetails = $hireSalesQuery->orderBy('created_at', 'desc')
                    ->paginate(10);

                if ($hireSalesDetails->isEmpty()) {
                    return response()->json([
                        'message' => 'Data Not Found',
                    ], 404);
                }

                return response()->json([
                    'hireSaleDetails' => $hireSalesDetails,
                ], 200);
            } elseif ($sales_type_id == 3) {

                $wholeSalesQuery = WholeProductSale::with([
                    'member',
                    'member.branchGroup',
                    'member.branchGroup.employee',
                    'member.branchGroup.employee.branchName',
                    'wholeProductSalesDetail',
                    'wholePayment'

                ]);



                if ($purchase_date && $updated_at) {
                    // Filter sales records where invoice_date is between purchase_date and updated_at
                    $wholeSalesQuery->whereBetween('invoice_date', [$purchase_date, $updated_at]);
                }

                if ($branch_id) {
                    $wholeSalesQuery->whereHas('member.branchGroup.employee.branchName', function ($query) use ($branch_id) {
                        $query->where('id', $branch_id);
                    });
                }



                $wholeSalesDetails = $wholeSalesQuery->orderBy('created_at', 'desc')
                    ->paginate(10);

                if ($wholeSalesDetails->isEmpty()) {
                    return response()->json([
                        'message' => 'Data Not Found',
                    ], 404);
                }

                return response()->json([
                    'wholeSaleDetails' => $wholeSalesDetails,
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
    }

    // public function allSalesSummaryReportFilter(Request $request){
    //     if(Auth::check()){
    //         $sales_type_id = $request->input('sales_type_id');
    //         $member_id = $request->input('member_id');
    //         $product_id = $request->input('product_id');
    //         $category_id = $request->input('category_id');
    //         $brand_id = $request->input('brand_id');
    //         $invoice_date = $request->input('invoice_date');
    //         $updated_at = $request->input('updated_at');
    //         $reporting_type_id = $request->input('reporting_type_id');
    //         $branch_group_id = $request->input('branch_group_id');



    //         if($sales_type_id == 1){

    //             $cashSalesQuery = CashSale::with([
    //                 'member',
    //                 'member.branchGroup',
    //                 'cashDetails' => function ($query) {
    //                     $query->select('id', 'cash_id', 'product_id');
    //                 },
    //                 'cashDetails',
    //                 'cashDetails.product',
    //                 'cashDetails.product.ProductBrand',
    //             ]);


    //             if ($member_id) {
    //                 $cashSalesQuery->where('member_id', $member_id);
    //             }
    //             if ($branch_group_id) {
    //                 $cashSalesQuery->whereHas('member.branchGroup', function ($query) use ($branch_group_id) {
    //                     $query->where('id', $branch_group_id);
    //                 });
    //             }
    //             if ($product_id) {
    //                 $cashSalesQuery->whereHas('cashDetails', function ($query) use ($product_id) {
    //                     $query->where('product_id', $product_id);
    //                 });
    //             }
    //             if ($category_id) {
    //                 $cashSalesQuery->whereHas('cashDetails.product', function ($query) use ($category_id) {
    //                     $query->where('category_id', $category_id);
    //                 });
    //             }
    //             if ($brand_id) {
    //                 $cashSalesQuery->whereHas('cashDetails.product', function ($query) use ($brand_id) {
    //                     $query->where('brand_id', $brand_id);
    //                 });
    //             }

    //             if ($invoice_date && $updated_at) {

    //                 $cashSalesQuery->whereBetween('invoice_date', [$invoice_date, $updated_at]);
    //             }

    //             $cashSales = $cashSalesQuery->get();


    //             return response()->json([
    //                 'cashSales' => $cashSales
    //             ]);


    //         }










    //     }
    //     else{
    //         return response()->json([
    //             'message' => 'Unauthorized'
    //         ], 401);
    //     }

    // }



    public function allSalesSummaryReportFilter(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $sales_type_id = $request->input('sales_type_id');
        $member_id = $request->input('member_id');
        $product_id = $request->input('product_id');
        $category_id = $request->input('category_id');
        $brand_id = $request->input('brand_id');
        $invoice_date = $request->input('invoice_date');
        $updated_at = $request->input('updated_at');
        $reporting_type_id = $request->input('reporting_type_id'); // Filter Type
        $branch_group_id = $request->input('branch_group_id');
        $employee_id = $request->input('employee_id');

        if ($sales_type_id == 1) {
            $cashSalesQuery = CashSale::with([
                'member',
                'member.branchGroup',
                'member.branchGroup.employee', // Employee inside branch group
                'cashDetails' => function ($query) {
                    $query->select('id', 'cash_id', 'product_id');
                },
                'cashDetails.product',
                'cashDetails.product.ProductBrand',
            ]);

            if ($category_id) {
                $cashSalesQuery->whereHas('cashDetails.product', function ($query) use ($category_id) {
                    $query->where('category_id', $category_id);
                });
            }
            if ($brand_id) {
                $cashSalesQuery->whereHas('cashDetails.product', function ($query) use ($brand_id) {
                    $query->where('brand_id', $brand_id);
                });
            }




            // **Filtering Based on Reporting Type**
            switch ($reporting_type_id) {
                case 1: // Date-Wise Report
                    if ($invoice_date && $updated_at) {
                        $cashSalesQuery->whereBetween('invoice_date', [$invoice_date, $updated_at]);
                    }
                    break;

                case 2: // Product-Wise Report
                    if ($product_id) {
                        $cashSalesQuery->whereHas('cashDetails', function ($query) use ($product_id) {
                            $query->where('product_id', $product_id);
                        });
                    }
                    break;

                case 3: // Member-Wise Report
                    if ($member_id) {
                        $cashSalesQuery->where('member_id', $member_id);
                    }
                    break;

                case 4: // Branch-Wise Report
                    if ($branch_group_id) {
                        $cashSalesQuery->whereHas('member.branchGroup', function ($query) use ($branch_group_id) {
                            $query->where('id', $branch_group_id);
                        });
                    }
                    break;

                case 5: // Group-Wise Report
                    if ($branch_group_id) {
                        $cashSalesQuery->whereHas('member.branchGroup', function ($query) use ($branch_group_id) {
                            $query->where('id', $branch_group_id);
                        });
                    }
                    break;

                case 6: // Employee-Wise Report
                    if ($employee_id) {
                        $cashSalesQuery->whereHas('member.branchGroup.employee', function ($query) use ($employee_id) {
                            $query->where('id', $employee_id);
                        });
                    }
                    break;

                default:
                    break;
            }

            $cashSales = $cashSalesQuery->get();

            return response()->json([
                'cashSales' => $cashSales
            ]);
        }
        if ($sales_type_id == 2) {
            $hireSalesQuery = HireProductSale::with([
                'member',
                'member.branchGroup',
                'member.branchGroup.employee', // Employee inside branch group
                'hireProductSaleDetail' => function ($query) {
                    $query->select('id', 'hire_product_sales_id', 'product_id');
                },
                'hireProductSaleDetail.product',
                'hireProductSaleDetail.product.ProductBrand',
            ]);

            if ($category_id) {
                $hireSalesQuery->whereHas('hireProductSaleDetail.product', function ($query) use ($category_id) {
                    $query->where('category_id', $category_id);
                });
            }
            if ($brand_id) {
                $hireSalesQuery->whereHas('hireProductSaleDetail.product', function ($query) use ($brand_id) {
                    $query->where('brand_id', $brand_id);
                });
            }




            // **Filtering Based on Reporting Type**
            switch ($reporting_type_id) {
                case 1: // Date-Wise Report
                    if ($invoice_date && $updated_at) {
                        $hireSalesQuery->whereBetween('invoice_date', [$invoice_date, $updated_at]);
                    }
                    break;

                case 2: // Product-Wise Report
                    if ($product_id) {
                        $hireSalesQuery->whereHas('hireProductSaleDetail', function ($query) use ($product_id) {
                            $query->where('product_id', $product_id);
                        });
                    }
                    break;

                case 3: // Member-Wise Report
                    if ($member_id) {
                        $hireSalesQuery->where('member_id', $member_id);
                    }
                    break;

                case 4: // Branch-Wise Report
                    if ($branch_group_id) {
                        $hireSalesQuery->whereHas('member.branchGroup', function ($query) use ($branch_group_id) {
                            $query->where('id', $branch_group_id);
                        });
                    }
                    break;

                case 5: // Group-Wise Report
                    if ($branch_group_id) {
                        $hireSalesQuery->whereHas('member.branchGroup', function ($query) use ($branch_group_id) {
                            $query->where('id', $branch_group_id);
                        });
                    }
                    break;

                case 6: // Employee-Wise Report
                    if ($employee_id) {
                        $hireSalesQuery->whereHas('member.branchGroup.employee', function ($query) use ($employee_id) {
                            $query->where('id', $employee_id);
                        });
                    }
                    break;

                default:
                    break;
            }

            $hireSaleDetails = $hireSalesQuery->get();

            return response()->json([
                'hireSales' => $hireSaleDetails
            ]);
        }


        if ($sales_type_id == 3) {
            $wholeSalesQuery = WholeProductSale::with([
                'wholeSalier',
                'wholeProductSalesDetail' => function ($query) {
                    $query->select('id', 'whole_product_sales_id', 'product_id');
                },
                'wholeProductSalesDetail.product',
                'wholeProductSalesDetail.product.ProductBrand',
            ]);

            if ($category_id) {
                $wholeSalesQuery->whereHas('wholeProductSalesDetail.product', function ($query) use ($category_id) {
                    $query->where('category_id', $category_id);
                });
            }
            if ($brand_id) {
                $wholeSalesQuery->whereHas('wholeProductSalesDetail.product', function ($query) use ($brand_id) {
                    $query->where('brand_id', $brand_id);
                });
            }




            // **Filtering Based on Reporting Type**
            switch ($reporting_type_id) {
                case 1: // Date-Wise Report
                    if ($invoice_date && $updated_at) {
                        $wholeSalesQuery->whereBetween('invoice_date', [$invoice_date, $updated_at]);
                    }
                    break;

                case 2: // Product-Wise Report
                    if ($product_id) {
                        $wholeSalesQuery->whereHas('wholeProductSalesDetail', function ($query) use ($product_id) {
                            $query->where('product_id', $product_id);
                        });
                    }
                    break;

                case 3: // Member-Wise Report
                    if ($member_id) {
                        $wholeSalesQuery->where('member_id', $member_id);
                    }
                    break;

                case 4: // Branch-Wise Report
                    if ($branch_group_id) {
                        $wholeSalesQuery->whereHas('member.branchGroup', function ($query) use ($branch_group_id) {
                            $query->where('id', $branch_group_id);
                        });
                    }
                    break;

                case 5: // Group-Wise Report
                    if ($branch_group_id) {
                        $wholeSalesQuery->whereHas('member.branchGroup', function ($query) use ($branch_group_id) {
                            $query->where('id', $branch_group_id);
                        });
                    }
                    break;

                case 6: // Employee-Wise Report
                    if ($employee_id) {
                        $wholeSalesQuery->whereHas('member.branchGroup.employee', function ($query) use ($employee_id) {
                            $query->where('id', $employee_id);
                        });
                    }
                    break;

                default:
                    break;
            }

            $wholeSaleDetails = $wholeSalesQuery->get();

            return response()->json([
                'wholeSales' => $wholeSaleDetails
            ]);
        }




        return response()->json([
            'message' => 'Invalid sales type'
        ], 400);
    }

}

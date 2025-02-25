<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\CreditAccount;

use App\Models\DebitAccount;
use App\Models\LedgerTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LedgerTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function ledgerTransactionIndex()
    {
        if (Auth::check()) {
            $data = LedgerTransaction::with(['accountTransactionType', 'accountTransactionLedger'])->latest()->paginate(10);
            return response()->json([
                'message' => 'Data get successfully',
                'data' => $data,
            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
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
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'transaction_type_id' => 'required|integer',
            'transaction_ledger_id' => 'required|integer|exists:account_ledgers,id',
            'transaction_amount' => 'required|string',
            'transaction_date' => 'required|date_format:d/m/Y',
            'doc_number_or_check' => 'required|string',
            'current_balance' => 'required|string',
            'receive_payer_name' => 'required|string',
            'receive_payer_phone' => 'required|string',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $request->merge([
            'transaction_date' => Carbon::createFromFormat('d/m/Y', $request->transaction_date)->format('Y-m-d'),
        ]);

        DB::beginTransaction();
        try {
            $ledger_transaction = LedgerTransaction::create([
                'transaction_type_id' => $request->transaction_type_id,
                'transaction_ledger_id' => $request->transaction_ledger_id,
                'transaction_amount' => $request->transaction_amount,
                'transaction_date' => $request->transaction_date,
                'doc_number_or_check' => $request->doc_number_or_check,
                'current_balance' => $request->current_balance,
                'receive_payer_name' => $request->receive_payer_name,
                'receive_payer_phone' => $request->receive_payer_phone,
                'comment' => $request->comment,
            ]);

            if ($request->transaction_type_id == 1) {

                $total_debit_account = DebitAccount::latest()->value('total_debit_amount') ?? 0;
                $total_debit_account += $request->transaction_amount;






                DebitAccount::create([
                    'ledger_transaction_id' => $ledger_transaction->id,
                    'transaction_amount' => $request->transaction_amount,
                    'current_balance' => $request->current_balance,
                    'total_debit_amount' => $total_debit_account,

                ]);
            } elseif ($request->transaction_type_id == 2) {


                $total_credit_account = CreditAccount::latest()->value('total_credit_amount') ?? 0;
                $total_credit_account += $request->transaction_amount;


                $total_credit_accout = $request->transaction_amount + $total_credit_account;
                CreditAccount::create([
                    'ledger_transaction_id' => $ledger_transaction->id,
                    'transaction_account' => $request->transaction_amount,
                    'current_balance' => $request->current_balance,
                    'total_credit_amount' => $total_credit_account,

                ]);
            }

            DB::commit();

            return response()->json([
                'success' => 'Transaction created successfully',
                'transaction' => $ledger_transaction,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred while creating transaction',
                'message' => $e->getMessage(),
            ], 400);
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
    // public function ledgerTransactionUpdate(Request $request, $ledger_transaction_id)
    // {
    //     if (!Auth::check()) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'transaction_type_id'   => 'required|integer',
    //         'transaction_ledger_id' => 'required|integer',
    //         'transaction_amount'    => 'required|string',
    //         'transaction_date'      => 'required|date_format:d/m/Y',
    //         'doc_number_or_check'   => 'required|string',
    //         'current_balance'       => 'required|string',
    //         'receive_payer_name'    => 'required|string',
    //         'receive_payer_phone'   => 'required|string',
    //         'comment'               => 'nullable|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 400);
    //     }

    //     // Convert the date format from d/m/Y to Y-m-d
    //     $request->merge([
    //         'transaction_date' => Carbon::createFromFormat('d/m/Y', $request->transaction_date)->format('Y-m-d'),
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         // Retrieve the ledger transaction record.
    //         $ledger_transaction = LedgerTransaction::findOrFail($ledger_transaction_id);

    //         // Update the ledger transaction with new values.
    //         $ledger_transaction->update([
    //             'transaction_type_id'   => $request->transaction_type_id,
    //             'transaction_ledger_id' => $request->transaction_ledger_id,
    //             'transaction_amount'    => $request->transaction_amount,
    //             'transaction_date'      => $request->transaction_date,
    //             'doc_number_or_check'   => $request->doc_number_or_check,
    //             'current_balance'       => $request->current_balance,
    //             'receive_payer_name'    => $request->receive_payer_name,
    //             'receive_payer_phone'   => $request->receive_payer_phone,
    //             'comment'               => $request->comment,
    //         ]);

    //         // Update the related account record (only the latest related update)
    //         if ($request->transaction_type_id == 1) {
    //             $debitAccount = DebitAccount::where('ledger_transaction_id', $ledger_transaction->id)->latest()->first();
    //             if ($debitAccount) {
    //                 $debitAccount->update([
    //                     'transaction_amount' => $request->transaction_amount,
    //                     'current_balance'    => $request->current_balance,
    //                 ]);
    //             }
    //         } elseif ($request->transaction_type_id == 2) {
    //             $creditAccount = CreditAccount::where('ledger_transaction_id', $ledger_transaction->id)->latest()->first();
    //             if ($creditAccount) {
    //                 $creditAccount->update([
    //                     'transaction_account' => $request->transaction_amount,
    //                     'current_balance'    => $request->current_balance,
    //                 ]);
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success'     => 'Transaction updated successfully',
    //             'transaction' => $ledger_transaction,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'error'   => 'An error occurred while updating the transaction',
    //             'message' => $e->getMessage(),
    //         ], 400);
    //     }
    // }

    public function ledgerTransactionUpdate(Request $request, $ledger_transaction_id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'transaction_type_id' => 'required|integer',
            'transaction_ledger_id' => 'required|integer',
            'transaction_amount' => 'required|string',
            'transaction_date' => 'required|date_format:d/m/Y',
            'doc_number_or_check' => 'required|string',
            'current_balance' => 'required|string',
            'receive_payer_name' => 'required|string',
            'receive_payer_phone' => 'required|string',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $request->merge([
            'transaction_date' => Carbon::createFromFormat('d/m/Y', $request->transaction_date)->format('Y-m-d'),
        ]);

        DB::beginTransaction();
        try {
            $ledger_transaction = LedgerTransaction::create([
                'transaction_type_id' => $request->transaction_type_id,
                'transaction_ledger_id' => $request->transaction_ledger_id,
                'transaction_amount' => $request->transaction_amount,
                'transaction_date' => $request->transaction_date,
                'doc_number_or_check' => $request->doc_number_or_check,
                'current_balance' => $request->current_balance,
                'receive_payer_name' => $request->receive_payer_name,
                'receive_payer_phone' => $request->receive_payer_phone,
                'comment' => $request->comment,
            ]);

            if ($request->transaction_type_id == 1) {
                DebitAccount::create([
                    'ledger_transaction_id' => $ledger_transaction->id,
                    'transaction_amount' => $request->transaction_amount,
                    'current_balance' => $request->current_balance,
                ]);
            } elseif ($request->transaction_type_id == 2) {
                CreditAccount::create([
                    'ledger_transaction_id' => $ledger_transaction->id,
                    'transaction_amount' => $request->transaction_amount,
                    'current_balance' => $request->current_balance,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => 'Transaction created successfully',
                'transaction' => $ledger_transaction,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred while creating transaction',
                'message' => $e->getMessage(),
            ], 400);
        }
    }






    // public function update(Request $request, $id)
    // {
    //     if (!Auth::check()) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'transaction_type_id' => 'required|integer',
    //         'transaction_ledger_id' => 'required|integer',
    //         'transaction_amount' => 'required|string',
    //         'transaction_date' => 'required|date_format:d/m/Y',
    //         'doc_number_or_check' => 'required|string',
    //         'current_balance' => 'required|string',
    //         'receive_payer_name' => 'required|string',
    //         'receive_payer_phone' => 'required|string',
    //         'comment' => 'nullable|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 400);
    //     }

    //     $request->merge([
    //         'transaction_date' => Carbon::createFromFormat('d/m/Y', $request->transaction_date)->format('Y-m-d'),
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         $latest_transaction = LedgerTransaction::latest()->first();

    //         if (!$latest_transaction || $latest_transaction->id != $id) {
    //             return response()->json(['error' => 'Only the latest transaction can be updated'], 403);
    //         }

    //         $latest_transaction->update($request->all());

    //         DB::commit();

    //         return response()->json([
    //             'success' => 'Transaction updated successfully',
    //             'transaction' => $latest_transaction,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'error' => 'An error occurred while updating transaction',
    //             'message' => $e->getMessage(),
    //         ], 400);
    //     }
    // }



    /**
     * Remove the specified resource from storage.
     */
    // public function ledgerTransactionDelete($ledger_transaction_id)
    // {
    //     if(Auth::check()){
    //         $ledger_transaction = LedgerTransaction::find($ledger_transaction_id)->latest();
    //         dd($ledger_transaction);
    //         if($ledger_transaction){
    //             $ledger_transaction->delete();
    //             return response()->json(['message'=>'Transaction deleted successfully']);


    //         }


    //     }
    //     else{
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }
    // }



    public function ledgerTransactionAllSearch(Request $request)
    {
        if (Auth::check()) {

            $search = $request->input('search');
            $transactions = LedgerTransaction::with('accountTransactionType', 'accountTransactionLedger')->where('doc_number_or_check', 'LIKE', '%' . $search . '%')
                ->orWhere('receive_payer_name', 'LIKE', '%' . $search . '%')

                ->latest()->paginate(10);

            return response()->json([
                'data' => $transactions

            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }


    public function transtionLedgerIdWishSearch(Request $request)
    {
        if (Auth::check()) {
            $transaction_type_id = $request->input('transaction_ledger_id');
            $transactions = LedgerTransaction::with('accountTransactionType', 'accountTransactionLedger')
                ->where('transaction_ledger_id', $transaction_type_id)
                ->latest()->paginate(10);

            return response()->json([
                'data' => $transactions

            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }


    public function ledgerTransactionEyeViewList($ledger_transaction_id)
    {
        if (Auth::check()) {
            $transactions = LedgerTransaction::with('accountTransactionType', 'accountTransactionLedger', 'debitAccount', 'creditAccount')
                ->where('id', $ledger_transaction_id)->first();

            return response()->json([
                'data' => $transactions

            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }



    public function transtionListFilter(Request $request)
    {
        if (Auth::check()) {
            $query = LedgerTransaction::query()->with([
                'accountTransactionType',
                'accountTransactionLedger',
                'debitAccount',
                'creditAccount'
            ]);

            // Apply filters only if values are provided
            if ($request->filled('transaction_type_id')) {
                $query->where('transaction_type_id', $request->transaction_type_id);
            }

            if ($request->filled('transaction_ledger_id')) {
                $query->where('transaction_ledger_id', $request->transaction_ledger_id);
            }

            // Ensure both dates exist before applying filter
            if ($request->filled('transaction_date') && $request->filled('upto_date')) {
                $transaction_date = $request->transaction_date . " 00:00:00"; // Start of the day
                $upto_date = $request->upto_date . " 23:59:59"; // End of the day
                $query->whereBetween('transaction_date', [$transaction_date, $upto_date]);
            }

            if ($request->filled('receive_payer_name')) {
                $query->where('receive_payer_name', 'LIKE', '%' . $request->receive_payer_name . '%');
            }

            if ($request->filled('receiver_payer_phone')) {
                $query->where('receive_payer_phone', $request->receiver_payer_phone);
            }

            // Get paginated results
            $transactions = $query->paginate(10);

            return response()->json([
                'data' => $transactions
            ]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }












    public function ledgerReportList(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $reporting_id = $request->input('reporting_id');
        $ledger_id    = $request->input('ledger_id');
        $created_at   = $request->input('created_at');
        $upto_date    = $request->input('upto_date');
        $branch_id = $request->input('branch_id');

        if (empty($ledger_id) && empty($reporting_id)) {
            return response()->json(['error' => 'Missing search parameters'], 400);
        }

        // Define debit and credit ledger categories.
        $debit_report  = [2, 3, 8];
        $credit_report = [1, 4, 5, 6, 7];

        if (!empty($ledger_id)) {
            if (!in_array($ledger_id, array_merge($debit_report, $credit_report))) {
                return response()->json(['error' => 'Invalid ledger_id'], 400);
            }

            // If ledger_id exists in debit report, fetch DebitAccount
            if (in_array($ledger_id, $debit_report)) {
                $query = DebitAccount::with('ledgerTransaction.accountTransactionLedger')
                    ->whereHas('ledgerTransaction', function ($q) use ($ledger_id) {
                        $q->where('transaction_ledger_id', $ledger_id);
                    });

                if (!empty($reporting_id)) {
                    $query->whereHas('ledgerTransaction.accountTransactionLedger', function ($q) use ($reporting_id) {
                        $q->where('accounting_reporting_id', $reporting_id);
                    })->orWhere('transaction_report_id', $reporting_id);
                }

                // Apply date filter correctly
                if (!empty($created_at) && !empty($upto_date)) {
                    $query->whereBetween('created_at', [
                        $created_at . " 00:00:00",
                        $upto_date . " 23:59:59"
                    ]);
                }

                $debit_account = $query->get();

                //calculte debit account
                $transaction_amount = $query->sum('transaction_amount');
                $total_product_purchase_amount = $query->sum('total_product_purchase_amount');
                $total_due_product_amount = $query->sum('due_product_amount');
                $return_amount = $query->sum('return_amount');

                $total_all_debit_amount = $transaction_amount +
                    $total_product_purchase_amount + $total_due_product_amount + $return_amount;




                return response()->json([
                    'debit_ledger_report' => $debit_account,
                    'transaction_amount' => $transaction_amount,
                    'total_product_purchase_amount' => $total_product_purchase_amount,
                    'total_due_product_amount' => $total_due_product_amount,
                    'return_amount' => $return_amount,
                    'total_all_debit_amount' => $total_all_debit_amount
                ]);
            }

            // If ledger_id exists in credit report, fetch CreditAccount
            elseif (in_array($ledger_id, $credit_report)) {
                $query = CreditAccount::with('ledgerTransaction.accountTransactionLedger','member.branchGroup.employee.branchName','supplier')
                    ->whereHas('ledgerTransaction', function ($q) use ($ledger_id) {
                        $q->where('transaction_ledger_id', $ledger_id);
                    });

                if (!empty($reporting_id)) {
                    $query->whereHas('ledgerTransaction.accountTransactionLedger', function ($q) use ($reporting_id) {
                        $q->where('accounting_reporting_id', $reporting_id);
                    })->orWhere('transaction_report_id', $reporting_id);
                }

                if (!empty($branch_id)) {
                    $query->whereHas('member.branchGroup.employee.branchName', function ($q) use ($reporting_id) {
                        $q->where('accounting_reporting_id', $$branch_id);
                    });
                }

                // Apply date filter correctly
                if (!empty($created_at) && !empty($upto_date)) {
                    $query->whereBetween('created_at', [
                        $created_at . " 00:00:00",
                        $upto_date . " 23:59:59"
                    ]);
                }

                $credit_account = $query->get();

                $transaction_account = $query->sum('transaction_account');
                $admission_fee = $query->sum('admission_fee');
                $other_fee = $query->sum('other_fee');
                $return_amount = $query->sum('return_amount');
                $damage_amount = $query->sum('damage_amount');
                $cash_sales_amount = $query->sum('cash_sales_amount');
                $whole_sales_amount = $query->sum('whole_sales_amount');
                $hire_sales_amount = $query->sum('hire_sales_amount');

                $total_all_credit_amount = $transaction_account + $admission_fee
                    + $other_fee + $return_amount + $damage_amount + $cash_sales_amount
                    + $whole_sales_amount + $hire_sales_amount;




                return response()->json([
                    'credit_ledger_report' => $credit_account,
                    'transaction_account' => $transaction_account,
                    'admission_fee'  => $admission_fee,
                    'other_fee' => $other_fee,
                    'return_amount' => $return_amount,
                    'damage_amount' => $damage_amount,
                    'cash_sales_amount' => $cash_sales_amount,
                    'whole_sales_amount' => $whole_sales_amount,
                    'hire_sales_amount' => $hire_sales_amount,
                    'total_all_credit_amount' => $total_all_credit_amount


                ]);
            }
        }

        // If only reporting_id is provided, fetch both DebitAccount and CreditAccount
        elseif (!empty($reporting_id)) {

            // $debit_query = DebitAccount::with('ledgerTransaction.accountTransactionLedger')
            //     ->whereHas('ledgerTransaction.accountTransactionLedger', function ($q) use ($reporting_id) {
            //         $q->where('accounting_reporting_id', $reporting_id);
            //     })->orWhere('transaction_report_id', $reporting_id);

            $credit_query = CreditAccount::with('ledgerTransaction.accountTransactionLedger','member.branchGroup.employee.branchName','supplier')
                ->whereHas('ledgerTransaction.accountTransactionLedger', function ($q) use ($reporting_id) {
                    $q->where('accounting_reporting_id', $reporting_id);
                })->orWhere('transaction_report_id', $reporting_id);



            // Apply date filters
            if (!empty($created_at) && !empty($upto_date)) {
                $debit_query->whereBetween('created_at', [
                    $created_at . " 00:00:00",
                    $upto_date . " 23:59:59"
                ]);

                $credit_query->whereBetween('created_at', [
                    $created_at . " 00:00:00",
                    $upto_date . " 23:59:59"
                ]);
            }


            // // For Debit Account -------------
            // $debit_account  = $debit_query->get();

            // $transaction_amount = $debit_query->sum('transaction_amount');
            // $total_product_purchase_amount = $debit_query->sum('total_product_purchase_amount');
            // $total_due_product_amount = $debit_query->sum('due_product_amount');
            // $return_amount = $debit_query->sum('return_amount');

            // $total_all_debit_amount = $transaction_amount +
            //     $total_product_purchase_amount + $total_due_product_amount + $return_amount;

            // if ($debit_account) {
            //     return response()->json([

            //         'debit_ledger_report'  => $debit_account,
            //         'transaction_amount' => $transaction_amount,
            //         'total_product_purchase_amount' => $total_product_purchase_amount,
            //         'total_due_product_amount' => $total_due_product_amount,
            //         'return_amount' => $return_amount,
            //         'total_all_debit_amount' => $total_all_debit_amount,



            //     ]);
            // }




            // For Credit Account ----------
            $credit_account = $credit_query->get();


            $transaction_account = $credit_query->sum('transaction_account');
            $admission_fee = $credit_query->sum('admission_fee');
            $other_fee = $credit_query->sum('other_fee');
            $return_amount = $credit_query->sum('return_amount');
            $damage_amount = $credit_query->sum('damage_amount');
            $cash_sales_amount = $credit_query->sum('cash_sales_amount');
            $whole_sales_amount = $credit_query->sum('whole_sales_amount');
            $hire_sales_amount = $credit_query->sum('hire_sales_amount');

            $total_all_credit_amount = $transaction_account + $admission_fee
                + $other_fee + $return_amount + $damage_amount + $cash_sales_amount
                + $whole_sales_amount + $hire_sales_amount;




            if ($credit_account->isNotEmpty()) {
                return response()->json([
                    'credit_ledger_report' => $credit_account,
                    'transaction_account' => $transaction_account,
                    'admission_fee'  => $admission_fee,
                    'other_fee' => $other_fee,
                    'return_amount' => $return_amount,
                    'damage_amount' => $damage_amount,
                    'cash_sales_amount' => $cash_sales_amount,
                    'whole_sales_amount' => $whole_sales_amount,
                    'hire_sales_amount' => $hire_sales_amount,
                    'total_all_credit_amount' => $total_all_credit_amount
                ]);
            } else {
                return response()->json(['error' => 'No credit data found for the provided reporting_id'], 404);
            }
        }
    }


//     public function ledgerReportList(Request $request)
// {
//     if (!Auth::check()) {
//         return response()->json(['error' => 'Unauthorized'], 401);
//     }

//     $reporting_id = $request->input('reporting_id');
//     $ledger_id    = $request->input('ledger_id');
//     $created_at   = $request->input('created_at');
//     $upto_date    = $request->input('upto_date');

//     if (empty($ledger_id) && empty($reporting_id)) {
//         return response()->json(['error' => 'Missing search parameters'], 400);
//     }

//     // Define debit and credit ledger categories.
//     $debit_report  = [2, 3, 8];
//     $credit_report = [1, 4, 5, 6, 7];

//     // Initialize the result variable
//     $result = [];

//     if (!empty($ledger_id)) {
//         // Check if the ledger_id exists in debit_report
//         if (in_array($ledger_id, $debit_report)) {
//             $query = DebitAccount::with('ledgerTransaction.accountTransactionLedger')
//                 ->whereHas('ledgerTransaction', function ($q) use ($ledger_id) {
//                     $q->where('transaction_ledger_id', $ledger_id);
//                 });

//             // Apply reporting_id condition if provided
//             if (!empty($reporting_id)) {
//                 $query->whereHas('ledgerTransaction.accountTransactionLedger', function ($q) use ($reporting_id) {
//                     $q->where('accounting_reporting_id', $reporting_id);
//                 })->orWhere('transaction_report_id', $reporting_id);
//             }

//             // Apply date filter if provided
//             if (!empty($created_at) && !empty($upto_date)) {
//                 $query->whereBetween('created_at', [
//                     $created_at . " 00:00:00",
//                     $upto_date . " 23:59:59"
//                 ]);
//             }

//             $debit_account = $query->get();

//             // Calculate debit account totals
//             $transaction_amount = $query->sum('transaction_amount');
//             $total_product_purchase_amount = $query->sum('total_product_purchase_amount');
//             $total_due_product_amount = $query->sum('due_product_amount');
//             $return_amount = $query->sum('return_amount');

//             $total_all_debit_amount = $transaction_amount + $total_product_purchase_amount + $total_due_product_amount + $return_amount;

//             $result = [
//                 'debit_ledger_report'  => $debit_account,
//                 'transaction_amount' => $transaction_amount,
//                 'total_product_purchase_amount' => $total_product_purchase_amount,
//                 'total_due_product_amount' => $total_due_product_amount,
//                 'return_amount' => $return_amount,
//                 'total_all_debit_amount' => $total_all_debit_amount,
//             ];
//         }
//         // Check if the ledger_id exists in credit_report
//         elseif (in_array($ledger_id, $credit_report)) {
//             $query = CreditAccount::with('ledgerTransaction.accountTransactionLedger','member','supplier')
//                 ->whereHas('ledgerTransaction', function ($q) use ($ledger_id) {
//                     $q->where('transaction_ledger_id', $ledger_id);
//                 });

//             // Apply reporting_id condition if provided
//             if (!empty($reporting_id)) {
//                 $query->whereHas('ledgerTransaction.accountTransactionLedger', function ($q) use ($reporting_id) {
//                     $q->where('accounting_reporting_id', $reporting_id);
//                 })->orWhere('transaction_report_id', $reporting_id);
//             }

//             // Apply date filter if provided
//             if (!empty($created_at) && !empty($upto_date)) {
//                 $query->whereBetween('created_at', [
//                     $created_at . " 00:00:00",
//                     $upto_date . " 23:59:59"
//                 ]);
//             }

//             $credit_account = $query->get();

//             // Calculate credit account totals
//             $transaction_account = $query->sum('transaction_account');
//             $admission_fee = $query->sum('admission_fee');
//             $other_fee = $query->sum('other_fee');
//             $return_amount = $query->sum('return_amount');
//             $damage_amount = $query->sum('damage_amount');
//             $cash_sales_amount = $query->sum('cash_sales_amount');
//             $whole_sales_amount = $query->sum('whole_sales_amount');
//             $hire_sales_amount = $query->sum('hire_sales_amount');

//             $total_all_credit_amount = $transaction_account + $admission_fee
//                 + $other_fee + $return_amount + $damage_amount + $cash_sales_amount
//                 + $whole_sales_amount + $hire_sales_amount;

//             $result = [
//                 'credit_ledger_report' => $credit_account,
//                 'transaction_account' => $transaction_account,
//                 'admission_fee'  => $admission_fee,
//                 'other_fee' => $other_fee,
//                 'return_amount' => $return_amount,
//                 'damage_amount' => $damage_amount,
//                 'cash_sales_amount' => $cash_sales_amount,
//                 'whole_sales_amount' => $whole_sales_amount,
//                 'hire_sales_amount' => $hire_sales_amount,
//                 'total_all_credit_amount' => $total_all_credit_amount
//             ];
//         }
//         // If neither debit nor credit
//         else {
//             return response()->json(['error' => 'Invalid ledger_id'], 400);
//         }
//     }

//     // If no ledger_id but reporting_id exists, fetch both debit and credit reports
//     elseif (!empty($reporting_id)) {
//         // Similar to the previous logic, fetch both reports based on reporting_id
//         $debit_query = DebitAccount::with('ledgerTransaction.accountTransactionLedger')
//             ->whereHas('ledgerTransaction.accountTransactionLedger', function ($q) use ($reporting_id) {
//                 $q->where('accounting_reporting_id', $reporting_id);
//             })->orWhere('transaction_report_id', $reporting_id);

//         $credit_query = CreditAccount::with('ledgerTransaction.accountTransactionLedger')
//             ->whereHas('ledgerTransaction.accountTransactionLedger', function ($q) use ($reporting_id) {
//                 $q->where('accounting_reporting_id', $reporting_id);
//             })->orWhere('transaction_report_id', $reporting_id);

//         // Apply date filters for both queries
//         if (!empty($created_at) && !empty($upto_date)) {
//             $debit_query->whereBetween('created_at', [
//                 $created_at . " 00:00:00",
//                 $upto_date . " 23:59:59"
//             ]);

//             $credit_query->whereBetween('created_at', [
//                 $created_at . " 00:00:00",
//                 $upto_date . " 23:59:59"
//             ]);
//         }

//         // For Debit Account
//         $debit_account = $debit_query->get();
//         $transaction_amount = $debit_query->sum('transaction_amount');
//         $total_product_purchase_amount = $debit_query->sum('total_product_purchase_amount');
//         $total_due_product_amount = $debit_query->sum('due_product_amount');
//         $return_amount = $debit_query->sum('return_amount');
//         $total_all_debit_amount = $transaction_amount + $total_product_purchase_amount + $total_due_product_amount + $return_amount;

//         // For Credit Account
//         $credit_account = $credit_query->get();
//         $transaction_account = $credit_query->sum('transaction_account');
//         $admission_fee = $credit_query->sum('admission_fee');
//         $other_fee = $credit_query->sum('other_fee');
//         $return_amount = $credit_query->sum('return_amount');
//         $damage_amount = $credit_query->sum('damage_amount');
//         $cash_sales_amount = $credit_query->sum('cash_sales_amount');
//         $whole_sales_amount = $credit_query->sum('whole_sales_amount');
//         $hire_sales_amount = $credit_query->sum('hire_sales_amount');
//         $total_all_credit_amount = $transaction_account + $admission_fee + $other_fee + $return_amount + $damage_amount + $cash_sales_amount + $whole_sales_amount + $hire_sales_amount;


//         if($credit_query){
//             return response()->json([
//                                 'credit_ledger_report' => $credit_account,
//                                 'transaction_account' => $transaction_account,
//                                 'admission_fee'  => $admission_fee,
//                                 'other_fee' => $other_fee,
//                                 'return_amount' => $return_amount,
//                                 'damage_amount' => $damage_amount,
//                                 'cash_sales_amount' => $cash_sales_amount,
//                                 'whole_sales_amount' => $whole_sales_amount,
//                                 'hire_sales_amount' => $hire_sales_amount,
//                                 'total_all_credit_amount' => $total_all_credit_amount
//                             ]);

//         }


//         // if($debit_query){

//         //     return response()->json([

//         //                         'debit_ledger_report'  => $debit_account,
//         //                         'transaction_amount' => $transaction_amount,
//         //                         'total_product_purchase_amount' => $total_product_purchase_amount,
//         //                         'total_due_product_amount' => $total_due_product_amount,
//         //                         'return_amount' => $return_amount,
//         //                         'total_all_debit_amount' => $total_all_debit_amount,



//         //                     ]);

//         // }


//         return response()->json([
//             'debit_ledger_report'  => $debit_account,
//             'credit_ledger_report' => $credit_account,
//             'total_all_debit_amount' => $total_all_debit_amount,
//             'total_all_credit_amount' => $total_all_credit_amount
//         ]);
//     }

//     // If no ledger_id and reporting_id is missing
//     return response()->json(['error' => 'Missing search parameters'], 400);
// }





}

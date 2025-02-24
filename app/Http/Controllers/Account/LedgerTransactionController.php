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
                    'total_credit_amount' => $total_credit_account
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

    public function ledgerTransactionUpdate(Request $request,$ledger_transaction_id)
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



        public function ledgerTransactionAllSearch(Request $request){
            if(Auth::check()){

                $search = $request->input('search');
                $transactions = LedgerTransaction::with('accountTransactionType','accountTransactionLedger')->where('doc_number_or_check','LIKE','%'.$search.'%')
                ->orWhere('receive_payer_name','LIKE','%'.$search.'%')

                 ->latest()->paginate(10);

                 return response()->json([
                    'data' => $transactions

                 ]);

            }
            else{
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }


        public function transtionLedgerIdWishSearch(Request $request){
            if(Auth::check()){
                $transaction_type_id = $request->input('transaction_ledger_id');
                $transactions = LedgerTransaction::with('accountTransactionType','accountTransactionLedger')
                ->where('transaction_ledger_id',$transaction_type_id)
                ->latest()->paginate(10);

                return response()->json([
                    'data' => $transactions

                ]);

            }
            else{
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }


        public function ledgerTransactionEyeViewList($ledger_transaction_id){
            if(Auth::check()){
                $transactions = LedgerTransaction::with('accountTransactionType','accountTransactionLedger','debitAccount','creditAccount')
                ->where('id',$ledger_transaction_id)->first();

                return response()->json([
                    'data' => $transactions

                ]);

            }
            else{
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }









}

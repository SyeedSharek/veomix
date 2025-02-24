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

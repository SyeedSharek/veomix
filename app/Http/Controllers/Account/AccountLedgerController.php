<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\AccountLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccountLedgerController extends Controller
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
    public function ledgerStore(Request $request)
    {
        if (Auth::check()) {
            $validator = Validator::make($request->all(), [
                'account_transaction_type_id' => 'integer|required|exists:account_transaction_types,id',
                'ledger_name' => 'string|required',
                'accounting_reporting_id' => 'integer|required',
                'current_balance' => 'string|required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ]);
            }

            $ledgerAccount = AccountLedger::create([
                'account_transaction_type_id' => $request->account_transaction_type_id,
                'ledger_name' => $request->ledger_name,
                'accounting_reporting_id' => $request->accounting_reporting_id,
                'current_balance' => $request->current_balance,
            ]);

            return response()->json([
                'message' => 'Account ledger created successfully',
                'data' => $ledgerAccount,
            ]);
        } else {
            return response()->json([
                'error' => 'Unathorized',
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
    public function ledgerAccountUpdate(Request $request, $account_ledger_id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'account_transaction_type_id' => 'integer|required|exists:account_transaction_types,id',
            'ledger_name' => 'string|required',
            'accounting_reporting_id' => 'integer|required',
            'current_balance' => 'string|required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $ledgerAccount = AccountLedger::where('id', $account_ledger_id)->first();

        if (!$ledgerAccount) {
            return response()->json(['error' => 'Ledger account not found'], 404);
        }

        $ledgerAccount->update([
            'account_transaction_type_id' => $request->account_transaction_type_id,
            'ledger_name' => $request->ledger_name,
            'accounting_reporting_id' => $request->accounting_reporting_id,
            'current_balance' => $request->current_balance,
        ]);

        return response()->json([
            'message' => 'Account ledger updated successfully',
            'data' => $ledgerAccount,
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function ledgerAcountDelete($account_ledger_id)
    {
        if (Auth::check()) {

            $ledgerAccount = AccountLedger::find($account_ledger_id);

            if ($ledgerAccount) {
                $ledgerAccount->delete();
                return response()->json([
                    'message' => 'Data deleted successfully!!',
                ]);
            }
            return response()->json([
                'error' => 'Account ledger not found',
            ]);
        } else {
            return response()->json([
                'error' => 'Unathorized',
            ], 400);
        }
    }


    public function ledgerAccountWishList($ledger_id)
    {
        if (Auth::check()) {
            $ledgerAccount = AccountLedger::where('id', $ledger_id)->first();
            if (!$ledgerAccount) {
                return response()->json([
                    'error' => 'Account ledger not found',
                ], 404);
            }


            return response()->json([
                'message' => 'Data retrieved successfully',
                'data' => $ledgerAccount,
            ]);
        } else {
            return response()->json([
                'error' => 'Unathorized',
            ], 400);
        }
    }

    public function allSearch(Request $request)
    {
        if (Auth::check()) {
            $search = $request->input('search');

            $ledgerAccounts = AccountLedger::where('ledger_name', 'LIKE', '%' . $search . '%')
                ->orWhere('created_at', 'LIKE', '%' . $search . '%')
                ->latest()
                ->paginate(10);

            return response()->json([
                'message' => 'Data retrieved successfully',
                'data' => $ledgerAccounts,
            ]);
        } else {
            return response()->json([
                'error' => 'Unathorized',
            ], 400);
        }
    }




    public function eyeViewList($ledger_id)
    {
        if (Auth::check()) {
            $ledgerAccount = AccountLedger::where('id', $ledger_id)->first();
            if (!$ledgerAccount) {
                return response()->json([
                    'error' => 'Account ledger not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Data retrieved successfully',
                'data' => $ledgerAccount,
            ]);
        } else {
            return response()->json([
                'error' => 'Unathorized',
            ], 400);
        }
    }


    // public function allShowDetails(){
    //     if(Auth::check()){
    //         $data = AccountLedger::with('ledgerTransaction')->get();
    //         return response()->json([
    //            'message' => 'Data retrieved successfully',
    //             'data' => $data,
    //         ]);
    //     } else {
    //         return response()->json([
    //            'message' => 'Unauthorized Access',

    //         ]);
    //     }
    // }













}

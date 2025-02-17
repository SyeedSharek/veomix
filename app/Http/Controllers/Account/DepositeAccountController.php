<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\DepositeAccount;
use App\Models\DepositeWithdrawalAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DepositeAccountController extends Controller
{




    public function depositeReceive(Request $request)
    {
        if (Auth::check()) {
            $authName = auth()->user()->name;

            $validator = Validator::make($request->all(), [
                'member_id' => 'required|integer|exists:member_manages,id',
                'deposite_ammount' => 'required|numeric',
                'deposite_date' => 'required|date_format:d/m/Y',
                'account_balance' => 'nullable|numeric',
                'document_number' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }


            $deposite_date = \Carbon\Carbon::createFromFormat('d/m/Y', $request->deposite_date)->format('Y-m-d');

            $member_id = $request->member_id;
            $deposite_amount = $request->deposite_ammount;
            $account_balance = $request->account_balance;

            $existingDeposit = DepositeAccount::where('member_id', $member_id)->first();

            $installment_count = DepositeAccount::where('member_id', $member_id)->count() + 1;

            $existingDeposit = DepositeAccount::where('member_id', $member_id)->latest()->first();

            if (!$existingDeposit) {

                $holder_number = mt_rand(100000, 999999);
                $account_balance = $deposite_amount;
            } else {

                $holder_number = $existingDeposit->holder_number;


                $account_balance = $existingDeposit->account_balance + $deposite_amount;
            }

            $deposit = DepositeAccount::create([
                'member_id' => $member_id,
                'deposite_ammount' => $deposite_amount,
                'deposite_date' => $deposite_date,
                'account_balance' => $account_balance,
                'document_number' => $request->document_number,
                'holder_number' => $holder_number,
                'total_installment' => $installment_count,
                'entry_by' => $authName,
            ]);

            $exitstingDepositWithdrawal = DepositeWithdrawalAccount::where('member_id', $member_id)->first();

            if ($exitstingDepositWithdrawal) {
                $old_deposite_amount = $exitstingDepositWithdrawal->deposite_total_amount;
                $deposite_total_amount = $old_deposite_amount + $deposite_amount;

                $old_deposite_installment = $exitstingDepositWithdrawal->total_deposite_installment;
                $new_installment = $old_deposite_installment + 1;

                $old_total_due_balance = $exitstingDepositWithdrawal->total_due_balance;
                $new_due_balance = $old_total_due_balance + $deposite_amount;

                // Update existing record
                $exitstingDepositWithdrawal->update([
                    'deposite_total_amount' => $new_due_balance,
                    'total_deposite_installment' => $new_installment,
                    'total_due_balance' => $new_due_balance
                ]);
            } else {
                // If no record exists, create a new one
                DepositeWithdrawalAccount::create([
                    'member_id' => $member_id,
                    'deposite_total_amount' => $deposite_amount,
                    'total_due_balance' => $deposite_amount,
                    'total_deposite_installment' => 1,
                ]);
            }





            return response()->json([
                'message' => 'Deposit recorded successfully',
                'deposit' => $deposit
            ], 201);
        } else {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }
    }

    public function showDepositReceiveDetails()
    {
        if (Auth::check()) {
            $deposits = DepositeAccount::with([
                'member'
            ])
                ->paginate(10);

            return response()->json([
                'data' => $deposits
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }
    }

    public function showDetailsMemberIdWish($member_id)
    {
        if (Auth::check()) {
            $deposits = DepositeAccount::with([
                'member'
            ])
                ->where('member_id', $member_id)
                ->paginate(10);

            $depositeWithdrawalDetails = DepositeWithdrawalAccount::where('member_id', $member_id)->first();

            return response()->json([
                'depositeDetails' => $deposits,
                'depositeWithdrawalDetails' => $depositeWithdrawalDetails
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }
    }









    // public function depositeReceiveUpdate(Request $request, $member_receive_id)
    // {
    //     if (!Auth::check()) {
    //         return response()->json(['error' => 'Unauthenticated'], 401);
    //     }

    //     $authName = auth()->user()->name;

    //     // Validate request data
    //     $validator = Validator::make($request->all(), [
    //         'deposite_ammount' => 'required|numeric',
    //         'deposite_date' => 'required|date_format:d/m/Y',
    //         'account_balance' => 'nullable|numeric',
    //         'document_number' => 'required|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 400);
    //     }

    //     // Convert deposit date to 'Y-m-d' format
    //     $deposite_date = \Carbon\Carbon::createFromFormat('d/m/Y', $request->deposite_date)->format('Y-m-d');

    //     $member_id = $request->member_id;
    //     $deposite_amount = $request->deposite_ammount;

    //     // Retrieve the latest deposit entry for this member
    //     $latestDeposit = DepositeAccount::where('member_id', $member_receive_id)->latest()->first();

    //     if (!$latestDeposit) {
    //         return response()->json(['error' => 'No deposit record found for this member'], 404);
    //     }

    //     // Calculate new account balance
    //     $new_account_balance = ($latestDeposit->account_balance - $latestDeposit->deposite_ammount) + $deposite_amount;

    //     // Update the deposit record
    //     $latestDeposit->update([
    //         'deposite_ammount' => $deposite_amount,
    //         'deposite_date' => $deposite_date,
    //         'account_balance' => $new_account_balance,
    //         'document_number' => $request->document_number,
    //         'edited_by' => $authName,
    //     ]);

    //     return response()->json([
    //         'message' => 'Deposit updated successfully',
    //         'deposit' => $latestDeposit
    //     ], 200);
    // }




    public function depositeReceiveUpdate(Request $request, $member_id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        try {
            $authName = auth()->user()->name;

            // Validate input
            $validator = Validator::make($request->all(), [
                'deposite_amount' => 'required|numeric',
                'deposite_date' => 'required|date_format:d/m/Y',
                'account_balance' => 'nullable|numeric',
                'document_number' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $deposite_date = \Carbon\Carbon::createFromFormat('d/m/Y', $request->deposite_date)->format('Y-m-d');
            $deposite_amount = (float) $request->deposite_amount;

            $latestDeposit = DepositeAccount::where('member_id', $member_id)->latest()->first();

            if (!$latestDeposit) {
                return response()->json(['error' => 'Deposit record not found'], 404);
            }


            $depositWithdrawal = DepositeWithdrawalAccount::where('member_id', $member_id)->latest()->first();

            if (!$depositWithdrawal) {
                return response()->json(['error' => 'Deposit withdrawal account not found'], 404);
            }

            DB::beginTransaction();


            $old_deposite_amount = (float) $latestDeposit->deposite_ammount;
            $deposite_difference = $deposite_amount - $old_deposite_amount;

            $new_account_balance = $latestDeposit->account_balance + $deposite_difference;

            $latestDeposit->update([
                'deposite_ammount' => $deposite_amount,
                'deposite_date' => $deposite_date,
                'account_balance' => $new_account_balance,
                'document_number' => $request->document_number,
                'edited_by' => $authName,
            ]);


            $depositWithdrawal->update([
                'deposite_total_amount' => $depositWithdrawal->deposite_total_amount + $deposite_difference,
                'total_due_balance' => $depositWithdrawal->total_due_balance + $deposite_difference,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Deposit record updated successfully',
                'updated_deposit' => $latestDeposit
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function allSearch(Request $request)
    {
        if (Auth::check()) {
            $search = $request->input('search');

            $deposite = DepositeAccount::with('member')
                ->where(function ($query) use ($search) {
                    $query->whereHas('member', function ($query) use ($search) {
                        $query->where('memberName_english', 'like', '%' . $search . '%');
                    });
                })
                ->paginate(10);
            return response()->json([
                'data' => $deposite
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }
    }

    public function deleteDeposite($member_receive_id)
    {
        if (Auth::check()) {

            $deposit = DepositeAccount::where('member_id', $member_receive_id)->latest()->first();


            if ($deposit) {
                $deposit->delete();
                return response()->json([
                    'message' => 'Deposit deleted successfully',
                    'deposit' => $deposit
                ], 200);
            } else {
                return response()->json([
                    'error' => 'Deposit not found'
                ], 404);
            }
        } else {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }
    }

    public function reportDetailsFilter(Request $request)
    {
        if (Auth::check()) {

            $branch_group_id = $request->branch_group_id;
            $employee_id = $request->employee_id;
            $member_id = $request->member_id;
            $deposite_date = $request->deposite_date;
            $updated_at = $request->updated_at;
            $branch_id = $request->branch_id;


            $depositeDetails = DepositeAccount::with([

                'member',
                'member.branchGroup',
                'member.branchGroup.employee',
                'member.branchGroup.employee.branchName',


            ]);

            if ($deposite_date && $updated_at) {
                // Filter sales records where invoice_date is between purchase_date and updated_at
                $depositeDetails->whereBetween('deposite_date', [$deposite_date, $updated_at]);
            }

            if ($branch_id) {
                $depositeDetails->whereHas('member.branchGroup.employee.branchName', function ($query) use ($branch_id) {
                    $query->where('id', $branch_id);
                });
            }

            if ($branch_group_id) {
                $depositeDetails->whereHas('member.branchGroup', function ($query) use ($branch_group_id) {
                    $query->where('id', $branch_group_id);
                });
            }

            if ($employee_id) {
                $depositeDetails->whereHas('member.branchGroup.employee', function ($query) use ($employee_id) {
                    $query->where('id', $employee_id);
                });
            }

            if ($member_id) {
                $depositeDetails->whereHas('member', function ($query) use ($member_id) {
                    $query->where('member_id', $member_id);
                });
            }


            $depositeDetails = $depositeDetails->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'depositeDetails' => $depositeDetails
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }
    }
}

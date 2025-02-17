<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\DepositeAccount;
use App\Models\DepositeWithdrawalAccount;
use App\Models\WithdrawalAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class WithDrawalAccountController extends Controller
{




    public function withdrawalReceiveStore(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        try {
            $authName = auth()->user()->name;

            $validator = Validator::make($request->all(), [
                'member_id' => 'required|integer|exists:member_manages,id',
                'withdrawal_amount' => 'required|string',
                'withdrawal_date' => 'required|date_format:d/m/Y',
                'account_balance' => 'nullable|string',
                'document_number' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $withdrawal_date = \Carbon\Carbon::createFromFormat('d/m/Y', $request->withdrawal_date)->format('Y-m-d');
            $member_id = $request->member_id;
            $withdrawal_amount = $request->withdrawal_amount;
            $account_balance = $request->account_balance;

            $existingDeposit = WithdrawalAccount::where('member_id', $member_id)->latest()->first();
            $installment_count = WithdrawalAccount::where('member_id', $member_id)->count() + 1;

            if (!$existingDeposit) {
                $holder_number = mt_rand(100000, 999999);
            } else {
                $holder_number = $existingDeposit->holder_number;
                $account_balance = $existingDeposit->account_balance + $withdrawal_amount;
            }

            $dueAmountCheck = DepositeWithdrawalAccount::where('member_id', $member_id)->latest()->first();

            if (!$dueAmountCheck || $dueAmountCheck->total_due_balance < $withdrawal_amount) {
                return response()->json(['message' => 'Insufficient Amount'], 400);
            }

            DB::beginTransaction(); // Start transaction

            // Insert withdrawal record
            $withdrawal = WithdrawalAccount::create([
                'member_id' => $member_id,
                'withdrawal_amount' => $withdrawal_amount,
                'withdrawal_date' => $withdrawal_date,
                'account_balance' => $account_balance,
                'document_number' => $request->document_number,
                'holder_number' => $holder_number,
                'withdrawal_installment' => $installment_count,
                'entry_by' => $authName,
            ]);

            $existingDepositWithdrawal = DepositeWithdrawalAccount::where('member_id', $member_id)->first();

            if ($existingDepositWithdrawal) {
                $new_total_due_balance = $existingDepositWithdrawal->total_due_balance - $withdrawal_amount;
                $new_installment = ($existingDepositWithdrawal->total_withdrawal_installment ?? 0) + 1;

                $withdrawal_total_amount = is_null($existingDepositWithdrawal->withdrawal_total_amount)
                    ? $withdrawal_amount
                    : $existingDepositWithdrawal->withdrawal_total_amount + $withdrawal_amount;

                // Update existing record
                $existingDepositWithdrawal->update([
                    'withdrawal_total_amount' => $withdrawal_total_amount,
                    'total_withdrawal_installment' => $new_installment,
                    'total_due_balance' => $new_total_due_balance
                ]);
            }

            DB::commit(); // Commit transaction

            return response()->json([
                'message' => 'Withdrawal recorded successfully',
                'withdrawal' => $withdrawal
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction in case of error
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function showWithdrawalReceiveDetails()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        try {

            $withdrawalReceiveDetails = WithdrawalAccount::with('member')->get();

            return response()->json([
                'withdrawalReceiveDetails' => $withdrawalReceiveDetails
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function showWithdrawalMemberIdWish($member_id)
    {
        if (Auth::check()) {
            $withdrawalReceiveDetails = WithdrawalAccount::with('member')
                ->where('member_id', $member_id)
                ->paginate(10);

            $depositeWithdrawalAccountDetails = DepositeWithdrawalAccount::where('member_id', $member_id)->first();

            return response()->json([
                'withdrawalReceiveDetails' => $withdrawalReceiveDetails,
                'depositeWithdrawalAccountDetails' => $depositeWithdrawalAccountDetails
            ], 200);
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }


    public function allSearch(Request $request)
    {
        if (Auth::check()) {
            $search = $request->input('search');

            $deposite = WithdrawalAccount::with('member')
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






    public function withdrawalDelete(Request $request, $member_id)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }

        try {
            // Validate input
            $withdrawal_amount = $request->input('withdrawal_amount');


            $withdrawal_delete = WithdrawalAccount::where('member_id', $member_id)->latest()->first();

            if (!$withdrawal_delete) {
                return response()->json([
                    'error' => 'Withdrawal not found'
                ], 404);
            }

            $withdrawal_delete->delete();


            $withdrawal = DepositeWithdrawalAccount::where('member_id', $member_id)->latest()->first();

            if ($withdrawal) {

                $withdrawal->withdrawal_total_amount = max(0, $withdrawal->withdrawal_total_amount - $withdrawal_amount);
                $withdrawal->total_due_balance = max(0, $withdrawal->total_due_balance + $withdrawal_amount);
                $withdrawal->total_withdrawal_installment = max(0, $withdrawal->total_withdrawal_installment - 1);


                $withdrawal->save();
            }

            return response()->json([
                'message' => 'Withdrawal deleted successfully',
                'deleted_withdrawal' => $withdrawal_delete
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function reportWithdrawalDetailsFilter(Request $request)
    {
        if (Auth::check()) {

            $branch_group_id = $request->branch_group_id;
            $employee_id = $request->employee_id;
            $member_id = $request->member_id;
            $withdraw_date = $request->withdraw_date;
            $updated_at = $request->updated_at;
            $branch_id = $request->branch_id;




            $withdrawalDetails = WithdrawalAccount::with([

                'member',
                'member.branchGroup',
                'member.branchGroup.employee',
                'member.branchGroup.employee.branchName',


            ]);

            if ($withdraw_date && $updated_at) {
                // Filter sales records where invoice_date is between purchase_date and updated_at
                $withdrawalDetails->whereBetween('withdrawal_date', [$withdraw_date, $updated_at]);
            }

            if ($branch_id) {
                $withdrawalDetails->whereHas('member.branchGroup.employee.branchName', function ($query) use ($branch_id) {
                    $query->where('id', $branch_id);
                });
            }

            if ($branch_group_id) {
                $withdrawalDetails->whereHas('member.branchGroup', function ($query) use ($branch_group_id) {
                    $query->where('id', $branch_group_id);
                });
            }

            if ($employee_id) {
                $withdrawalDetails->whereHas('member.branchGroup.employee', function ($query) use ($employee_id) {
                    $query->where('id', $employee_id);
                });
            }

            if ($member_id) {
                $withdrawalDetails->whereHas('member', function ($query) use ($member_id) {
                    $query->where('member_id', $member_id);
                });
            }


            $withdrawReportDetails = $withdrawalDetails->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'withdrawReportDetails' => $withdrawReportDetails
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }
    }




    public function withdrawalReceiveUpdate(Request $request, $member_id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        try {
            $authName = auth()->user()->name;

            // Validate input
            $validator = Validator::make($request->all(), [
                'withdrawal_amount' => 'required|numeric',
                'withdrawal_date' => 'required|date_format:d/m/Y',
                'account_balance' => 'nullable|numeric',
                'document_number' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $withdrawal_date = \Carbon\Carbon::createFromFormat('d/m/Y', $request->withdrawal_date)->format('Y-m-d');
            $withdrawal_amount = (float) $request->withdrawal_amount;
            $account_balance = (float) ($request->account_balance ?? 0);

            // Get the latest withdrawal record
            $latestWithdrawal = WithdrawalAccount::where('member_id', $member_id)->latest()->first();

            if (!$latestWithdrawal) {
                return response()->json(['error' => 'Withdrawal record not found'], 404);
            }

            // Get the deposit withdrawal account for balance adjustments
            $depositWithdrawal = DepositeWithdrawalAccount::where('member_id', $member_id)->latest()->first();

            if (!$depositWithdrawal) {
                return response()->json(['error' => 'Deposit account not found'], 404);
            }

            // Check if there is enough balance to update
            if ($withdrawal_amount > $depositWithdrawal->total_due_balance) {
                return response()->json(['error' => 'Insufficient funds'], 400);
            }

            DB::beginTransaction(); // Start transaction

            // Calculate new balance and installment
            $old_withdrawal_amount = (float) $latestWithdrawal->withdrawal_amount;
            $withdrawal_difference = $withdrawal_amount - $old_withdrawal_amount;
            $new_account_balance = $latestWithdrawal->account_balance + $withdrawal_difference;

            // dd($withdrawal_difference);

            // Update the withdrawal record
            $latestWithdrawal->update([
                'withdrawal_amount' => $withdrawal_amount,
                'withdrawal_date' => $withdrawal_date,
                'account_balance' => $new_account_balance,
                'document_number' => $request->document_number,
                'edited_by' => $authName,
            ]);

            // Adjust deposit withdrawal account
            $depositWithdrawal->update([
                'withdrawal_total_amount' => $depositWithdrawal->withdrawal_total_amount + $withdrawal_difference,
                'total_due_balance' => $depositWithdrawal->total_due_balance - $withdrawal_difference,
            ]);

            DB::commit(); // Commit transaction

            return response()->json([
                'message' => 'Withdrawal record updated successfully',
                'updated_withdrawal' => $latestWithdrawal
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction in case of error
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage()
            ], 500);
        }
    }


}

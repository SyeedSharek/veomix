<?php

namespace App\Http\Controllers\HireTransaction;

use App\Http\Controllers\Controller;
use App\Models\InstallmentManage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseFormatSame;

class HireTransactionController extends Controller
{
    public function receiveHireTransaction(Request $request)
    {
        if (Auth::check()) {

            $authName = auth()->user()->name;


            $validator = Validator::make($request->all(), [
                'member_id' => 'required|integer|exists:member_manages,id',
                'invoice_number' => 'required|string',
                'paid_installment_loan' => 'required|string',
                'due_amount' => 'required|string',
                'due_installment' => 'required|string',
                'installment_date' => 'required|date_format:d/m/Y',
                'installment_expired_date' => 'required|date_format:d/m/Y',
                'penalty_amount' => 'string',

            ]);
            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 400);
            }

            $request->merge([
                'installment_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->installment_date)->format('Y-m-d'),
                'installment_expired_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->installment_expired_date)->format('Y-m-d'),



            ]);


            $total_amount = InstallmentManage::where('member_id', $request->member_id)
                ->where('invoice_number', $request->invoice_number)
                ->value('total_amount');

            // dd($total_amount);

            $hire_total_due_amount = InstallmentManage::where('member_id', $request->member_id)
                ->where('invoice_number', $request->invoice_number)
                ->value('hire_total_due_amount');


            $total_installment  = InstallmentManage::where('member_id', $request->member_id)
                ->where('invoice_number', $request->invoice_number)
                ->value('total_installment');





            $installment = InstallmentManage::create([
                'member_id' => $request->member_id,
                'hire_loan_manage_id' => $request->hire_loan_manage_id,
                'invoice_number' => $request->invoice_number,
                'total_amount' => $total_amount,
                'paid_installment_loan' => $request->paid_installment_loan,
                'due_amount' => $request->due_amount,
                'due_installment' => $request->due_installment,
                'installment_date' => $request->installment_date,
                'installment_expired_date' => $request->installment_expired_date,
                'penalty_amount' => $request->penalty_amount,
                'hire_total_due_amount' => $hire_total_due_amount,
                'total_installment' => $total_installment,
                'entry_name' => $authName,



            ]);

            return response()->json([
                'message' => 'Hire transaction received successfully',
                'installment_id' => $installment
            ], 201);
        } else {
            return response()->json([
                'error' => 'Unathorised'
            ], 400);
        }
    }


    public function hireInstallmentSearch(Request $request)
    {
        if (Auth::check()) {
            $sales_type_id = $request->input('sales_type_id');
            $installment_date = $request->input('installment_date');
            $updated_at = $request->input('updated_at');
            $branch_id = $request->input('branch_id');


            if ($sales_type_id == 2) {
                $installmentQuery = InstallmentManage::with([

                    'member',
                    'member.branchGroup',
                    'member.branchGroup.employee',
                    'member.branchGroup.employee.branchName',
                    'hireLoanManage'

                ]);



                if ($installment_date && $updated_at) {
                    // Filter sales records where invoice_date is between purchase_date and updated_at
                    $installmentQuery->whereBetween('installment_date', [$installment_date, $updated_at]);
                }

                if ($branch_id) {
                    $installmentQuery->whereHas('member.branchGroup.employee.branchName', function ($query) use ($branch_id) {
                        $query->where('id', $branch_id);
                    });
                }



                $installmentDetails = $installmentQuery->orderBy('created_at', 'desc')
                    ->paginate(10);

                if ($installmentDetails->isEmpty()) {
                    return response()->json([
                        'message' => 'Data Not Found',
                    ], 404);
                }

                return response()->json([
                    'cashSaleDetails' => $installmentDetails,
                ], 200);
            }
        } else {
            return response()->json([
                'error' => 'Unathorised'
            ], 400);
        }
    }

    public function allInstallmentSearch(Request $request)
    {
        if (Auth::check()) {
            $search = $request->input('search');

            $installmentQuery = InstallmentManage::with([

                'member',
                'member.branchGroup',
                'member.branchGroup.employee',
                'member.branchGroup.employee.branchName',
                'hireLoanManage',
            ])
                ->where(function ($query) use ($search) {
                    $query->whereHas('member', function ($query) use ($search) {
                        $query->where('memberName_english', 'like', '%' . $search . '%');
                    });
                })
                ->paginate(10);

            if ($installmentQuery->isEmpty()) {
                return response()->json([
                    'message' => 'No data found',
                ], 404);
            }

            return response()->json([
                'installmentDetails' => $installmentQuery,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }

    public function index()
    {
        if (Auth::check()) {
            $installmentDetails = InstallmentManage::with([
                'member:id,memberName_english',
            ])
                ->paginate(10);

            if ($installmentDetails->isEmpty()) {
                return response()->json([
                    'message' => 'No data found',
                ], 404);
            }

            return response()->json([
                'installmentDetails' => $installmentDetails,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }

    public function update(Request $request, $installment_manage_id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $authName = auth()->user()->name;

        $validator = Validator::make($request->all(), [
            'member_id' => 'required|integer|exists:member_manages,id',
            'invoice_number' => 'required|string',
            'paid_installment_loan' => 'required|string',
            'due_amount' => 'required|string',
            'due_installment' => 'required|string',
            'installment_date' => 'required|date_format:d/m/Y',
            'installment_expired_date' => 'required|date_format:d/m/Y',
            'penalty_amount' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Convert date formats
        $request->merge([
            'installment_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->installment_date)->format('Y-m-d'),
            'installment_expired_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->installment_expired_date)->format('Y-m-d'),
        ]);

        // Find the installment record
        $installment = InstallmentManage::find($installment_manage_id);
        if (!$installment) {
            return response()->json(['error' => 'Installment record not found'], 404);
        }

        // Update the installment details
        $installment->update([
            'paid_installment_loan' => $request->paid_installment_loan,
            'due_amount' => $request->due_amount,
            'due_installment' => $request->due_installment,
            'installment_date' => $request->installment_date,
            'installment_expired_date' => $request->installment_expired_date,
            'penalty_amount' => $request->penalty_amount,
            'entry_name' => $authName,
        ]);

        return response()->json([
            'message' => 'Installment updated successfully',
            'installment' => $installment
        ], 200);
    }


    public function destroy($installment_manage_id)
    {
        if (Auth::check()) {
            $installment = InstallmentManage::find($installment_manage_id);

            $installment->delete();

            return response()->json(['message' => 'Installment deleted successfully'], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }

    public function memberIdWishShowData($member_id)
    {
        if (Auth::check()) {
            $installment = InstallmentManage::where('member_id', $member_id)->paginate(10);

            return response()->json([
                'installmentDetails' => $installment
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }


    public function collectionSheetSearch(Request $request)
    {
        if (Auth::check()) {
            $sales_type_id = $request->input('sales_type_id');
            $installment_date = $request->input('installment_date');
            $updated_at = $request->input('updated_at');
            $branch_id = $request->input('branch_id');
            $branch_group_id = $request->input('branch_group_id');
            $extra_row = $request->input('extra_row');




            if ($sales_type_id == 2) {
                $installmentQuery = InstallmentManage::with([

                    'member',
                    'member.branchGroup',
                    'member.branchGroup.employee',
                    'member.branchGroup.employee.branchName',
                    'hireLoanManage'

                ]);


                if ($installment_date && $updated_at) {
                    // Filter sales records where invoice_date is between purchase_date and updated_at
                    $installmentQuery->whereBetween('installment_date', [$installment_date, $updated_at]);
                }

                if ($branch_group_id) {
                    $installmentQuery->whereHas('member.branchGroup', function ($query) use ($branch_group_id) {
                        $query->where('id', $branch_group_id);
                    });
                }
                if ($branch_id) {
                    $installmentQuery->whereHas('member.branchGroup.employee.branchName', function ($query) use ($branch_id) {
                        $query->where('id', $branch_id);
                    });
                }

                $installmentDetails = $installmentQuery->orderBy('created_at', 'desc')
                    ->paginate(10);

                if ($installmentDetails->isEmpty()) {
                    return response()->json([
                        'message' => 'Data Not Found',
                    ], 404);
                }

                return response()->json([
                    'cashSaleDetails' => $installmentDetails,
                ], 200);
            }
        } else {
            return response()->json([
                'error' => 'Unathorised'
            ], 400);
        }
    }


    public function collectionSheetAllSearch(Request $request)
    {
        if (Auth::check()) {
            $search = $request->input('search');

            $installmentQuery = InstallmentManage::with([
                'member',
                'member.branchGroup',
                'member.branchGroup.employee',
                'member.branchGroup.employee.branchName',
                'hireLoanManage'
            ])
                ->where(function ($query) use ($search) {
                    $query->whereHas('member.branchGroup', function ($query) use ($search) {
                        $query->where('group_name', 'like', '%' . $search . '%');
                    })
                        ->orWhereHas('member.branchGroup.employee.branchName', function ($query) use ($search) {
                            $query->where('name', 'like', '%' . $search . '%');
                        });
                })
                ->paginate(10);

            if ($installmentQuery->isEmpty()) {
                return response()->json([
                    'message' => 'No data found',
                ], 404);
            }

            return response()->json([
                'installmentDetails' => $installmentQuery,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }


    public function groupListFilterSearch(Request $request)
    {
        if (Auth::check()) {
            // Retrieve filter parameters from the request
            $installment_date = $request->input('installment_date');
            $updated_at = $request->input('updated_at');
            $branch_id = $request->input('branch_id');

            // Initialize the query with necessary relationships
            $installmentDetails = InstallmentManage::with([
                // 'member:id,memberName_english',
                'member:id,memberName_english,banchGroup_id',
                'member.branchGroup:id,group_name,employee_id',
                'member.branchGroup.employee:id,employeeName,branch_manage_id',
                'member.branchGroup.employee.branchName:id,name',


            ]);


            if ($installment_date && $updated_at) {

                $installmentDetails->whereBetween('installment_date', [$installment_date, $updated_at]);
            }


            if ($branch_id) {
                $installmentDetails->whereHas('member.branchGroup.employee.branchName', function ($query) use ($branch_id) {
                    $query->where('id', $branch_id);
                });
            }

            // Paginate the results
            $installmentDetails = $installmentDetails->paginate(10);

            // Check if data is found
            if ($installmentDetails->isEmpty()) {
                return response()->json([
                    'message' => 'No data found',
                ], 404);
            }

            // Return the filtered installment details
            return response()->json([
                'installmentDetails' => $installmentDetails,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }


    public function installmentIdWishEyeView($installment_id)
    {
        if (Auth::check()) {
            $installment = InstallmentManage::with([
                'hireLoanManage',
                'member',
                'member.branchGroup',
                'member.branchGroup.employee',
                'member.branchGroup.employee.branchName',
            ])
                ->where('id', $installment_id)
                ->first();

            if ($installment) {
                return response()->json([
                    'installment' => $installment
                ], 200);
            } else {
                return response()->json([
                    'error' => 'Installment record not found'
                ], 404);
            }
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }

    public function groupListIndex()
    {
        if (Auth::check()) {
            $installmentDetails = InstallmentManage::with([
                'member:id,memberName_english,banchGroup_id',
                'member.branchGroup:id,group_name,employee_id',
                'member.branchGroup.employee:id,employeeName,branch_manage_id',
                'member.branchGroup.employee.branchName:id,name',
            ])
                ->latest()
                ->paginate(10);

            return response()->json([
                'installmentDetails' => $installmentDetails,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }

    public function allGrouplistSearch(Request $request)
    {
        if (Auth::check()) {
            $search = $request->input('search');

            $installmentDetails = InstallmentManage::with([
                'member',
                'member.branchGroup:id,group_name,employee_id',
                'member.branchGroup.employee:id,employeeName,branch_manage_id',
                'member.branchGroup.employee.branchName:id,name',
            ])
                ->where(function ($query) use ($search) {
                    $query->whereHas('member', function ($query) use ($search) {
                        $query->where('memberName_english', 'like', '%' . $search . '%');
                    })
                        ->orWhereHas('member.branchGroup.employee', function ($query) use ($search) {
                            $query->where('employeeName', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('member.branchGroup', function ($query) use ($search) {
                            $query->where('group_name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('member.branchGroup.employee.branchName', function ($query) use ($search) {
                            $query->where('name', 'like', '%' . $search . '%');
                        });
                })
                ->paginate(10);

            if ($installmentDetails->isEmpty()) {
                return response()->json([
                    'message' => 'No data found',
                ], 404);
            }

            return response()->json([
                'installmentDetails' => $installmentDetails,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }


    public function branchGroupIdWishSearch($branch_group_id)
    {
        if (Auth::check()) {

            $installmentDetails = InstallmentManage::with([
                'member',
                'member.branchGroup:id,group_name,employee_id',
                'member.branchGroup.employee:id,employeeName,branch_manage_id',
                'member.branchGroup.employee.branchName:id,name',
            ])
                ->whereHas('member.branchGroup', function ($query) use ($branch_group_id) {
                    $query->where('id', $branch_group_id); // Filter by branch_group_id
                })
                ->paginate(10);

            if ($installmentDetails->isEmpty()) {
                return response()->json([
                    'message' => 'No data found',
                ], 404);
            }

            return response()->json([
                'installmentDetails' => $installmentDetails,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }

    public function installmentReportSearchFilter(Request $request)
    {
        if (Auth::check()) {

            $branch_group_id = $request->input('branch_group_id');
            $installment_date = $request->input('installment_date');
            $updated_at = $request->input('updated_at');
            $employee_id = $request->input('employee_id');
            $member_id = $request->input('member_id');
            $branch_id = $request->input('branch_id');

            $installmentQuery = InstallmentManage::with([

                'member',
                'member.branchGroup',
                'member.branchGroup.employee',
                'member.branchGroup.employee.branchName',
                'hireLoanManage'

            ]);


            if ($installment_date && $updated_at) {
                // Filter sales records where invoice_date is between purchase_date and updated_at
                $installmentQuery->whereBetween('installment_date', [$installment_date, $updated_at]);
            }

            if ($branch_group_id) {
                $installmentQuery->whereHas('member.branchGroup', function ($query) use ($branch_group_id) {
                    $query->where('id', $branch_group_id);
                });
            }
            if ($employee_id) {
                $installmentQuery->whereHas('member.branchGroup.employee', function ($query) use ($employee_id) {
                    $query->where('id', $employee_id);
                });
            }

            if ($member_id) {
                $installmentQuery->whereHas('member', function ($query) use ($member_id) {
                    $query->where('id', $member_id);
                });
            }


            if ($branch_id) {
                $installmentQuery->whereHas('member.branchGroup.employee.branchName', function ($query) use ($branch_id) {
                    $query->where('id', $branch_id);
                });
            }


            $installmentDetails = $installmentQuery->orderBy('created_at', 'desc')
                ->paginate(10);

            if ($installmentDetails->isEmpty()) {
                return response()->json([
                    'message' => 'Data Not Found',
                ], 404);
            }

            return response()->json([
                'cashSaleDetails' => $installmentDetails,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }

    public function installmentDetailsReportFilter(Request $request)
    {
        if (Auth::check()) {
            $installment_date = $request->input('installment_date');
            $updated_at = $request->input('updated_at');
            $branch_id = $request->input('branch_id');


            $installmentQuery = InstallmentManage::with([

                'member',
                'member.branchGroup',
                'member.branchGroup.employee',
                'member.branchGroup.employee.branchName',
                'hireLoanManage'

            ]);


            if ($installment_date && $updated_at) {

                $installmentQuery->whereBetween('installment_date', [$installment_date, $updated_at]);
            }

            if ($branch_id) {
                $installmentQuery->whereHas('member.branchGroup.employee.branchName', function ($query) use ($branch_id) {
                    $query->where('id', $branch_id);
                });
            }


            $installmentDetails = $installmentQuery->orderBy('created_at', 'desc')
                ->paginate(10);

            if ($installmentDetails->isEmpty()) {
                return response()->json([
                    'message' => 'Data Not Found',
                ], 404);
            }

            return response()->json([
                'cashSaleDetails' => $installmentDetails,
            ], 200);
        }
        else
        {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }




    public function allReportDetailsSearch(Request $request)
    {
        if (Auth::check()) {
            $search = $request->input('search');

            $installmentDetails = InstallmentManage::with([
                'member',
                'member.branchGroup:id,group_name,employee_id',
                'member.branchGroup.employee:id,employeeName,branch_manage_id',
                'member.branchGroup.employee.branchName:id,name',
            ])
                ->where(function ($query) use ($search) {
                    $query->whereHas('member', function ($query) use ($search) {
                        $query->where('memberName_english', 'like', '%' . $search . '%');
                    })
                        ->orWhereHas('member.branchGroup.employee', function ($query) use ($search) {
                            $query->where('employeeName', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('member.branchGroup', function ($query) use ($search) {
                            $query->where('group_name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('member.branchGroup.employee.branchName', function ($query) use ($search) {
                            $query->where('name', 'like', '%' . $search . '%');
                        });
                })
                ->paginate(10);

            if ($installmentDetails->isEmpty()) {
                return response()->json([
                    'message' => 'No data found',
                ], 404);
            }

            return response()->json([
                'installmentDetails' => $installmentDetails,
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
    }






}

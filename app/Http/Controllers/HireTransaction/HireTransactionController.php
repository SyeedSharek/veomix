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
    public function receiveHireTransaction(Request $request){
        if(Auth::check()){

            $authName = auth()->user()->name;


            $validator = Validator::make($request->all(),[
                'member_id'=> 'required|integer|exists:member_manages,id',
                'invoice_number' => 'required|string',
                'paid_installment_loan' => 'required|string',
                'due_amount' => 'required|string',
                'due_installment' => 'required|string',
                'installment_date' =>'required|date_format:d/m/Y',
                'installment_expired_date' => 'required|date_format:d/m/Y',
                'penalty_amount' =>'string',

            ]);
            if($validator->fails()){
                return response()->json([
                    'error' => $validator->errors()
                ],400);
            }

            $request->merge([
                'installment_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->installment_date)->format('Y-m-d'),
                'installment_expired_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->installment_expired_date)->format('Y-m-d'),



            ]);


            $total_amount = InstallmentManage::where('member_id', $request->member_id)
            ->where('invoice_number', $request->invoice_number)
            ->value('total_amount');

            $total_due_amount = InstallmentManage::where('member_id', $request->member_id)
            ->where('invoice_number', $request->invoice_number)
            ->value('total_due_amount');

            $total_installment = $total_due_amount = InstallmentManage::where('member_id', $request->member_id)
            ->where('invoice_number', $request->invoice_number)
            ->value('total_installment');



            $installment = InstallmentManage::create([
                'member_id' => $request->member_id,
                'invoice_number' => $request->invoice_number,
                'total_amount' => $total_amount,
                'paid_installment_loan' => $request->paid_installment_loan,
                'due_amount' => $request->due_amount,
                'due_installment' => $request->due_installment,
                'installment_date' => $request->installment_date,
                'installment_expired_date' => $request->installment_expired_date,
                'penalty_amount' => $request->penalty_amount,
                'total_due_amount' => $total_due_amount,
                'total_installment' => $total_installment,
                'entry_name' => $authName,


            ]);

            return response()->json([
               'message' => 'Hire transaction received successfully',
                'installment_id' => $installment
            ],201);

        }
        else{
            return response()->json([
                'error' => 'Unathorised'
            ],400);
        }
    }




}

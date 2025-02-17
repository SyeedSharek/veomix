<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashSale;
use App\Models\HireProductSale;
use App\Models\InstallmentManage;
use App\Models\MemberManage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class MemberManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $data = MemberManage::with(['branchGroup','bloodGroup','gender','religion','education','meritalStatus'])->latest()->get();
            return response()->json([
                'message' => 'Data get successfully',
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'errors' => 'Unauthorized',
            ]);
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
        if (Auth::check()) {

            $request->merge([
                'openingDate' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->openingDate)->format('Y-m-d'),
                'dataOfBirth' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->dataOfBirth)->format('Y-m-d'),
            ]);

            $random_id = rand(1000, 9999);

            while (MemberManage::where('member_card_id', $random_id)->exists()) {

                $random_id = rand(1000, 9999);
            }



            $validator = Validator::make($request->all(), [
                'member_card_id' => 'unique:members,member_card_id',
                'memberName_english' => 'required|string',
                'memberName_bangla' => 'required|string',
                'banchGroup_id' => 'nullable|exists:branch_groups,id',
                'phoneNumber' => 'required|string',
                'fatherName' => 'required|string',
                'motherName' => 'required|string',
                'spouseName' => 'nullable|string',
                'openingDate' => 'required|date',
                'refferedBy' => 'nullable|string',
                'nationaId' => 'nullable|string',
                'birthCertificate' => 'nullable|string',
                'email' => 'nullable|email',
                'bloodGroup_Id' => 'nullable|exists:blood_groups,id',
                'gender_Id' => 'required|exists:genders,id',
                'religion_id' => 'nullable|exists:riligions,id',
                'maritalStatus_id' => 'nullable|exists:marital_statuses,id',
                'dataOfBirth' => 'nullable|date',
                'present_address' => 'required|string|max:500',
                'permanent_address' => 'required|string|max:500',
                'monthlyIncome' => 'required|string|max:100',
                'education_id' => 'nullable|exists:education,id',
                'profession' => 'required|string|max:255',
                'admissionFees' => 'nullable|numeric',
                'otherFees' => 'nullable|numeric',
                'member_profiles' => 'nullable',
                'member_signature' => 'nullable',
                'nomineeName' => 'required|string|max:255',
                'nomineeFather' => 'nullable|string|max:255',
                'nomineeMother' => 'nullable|string|max:255',
                'nomineePhone' => 'nullable|string|max:15',
                'nomineeRelation' => 'nullable|string|max:255',
                'nomineeNationId' => 'nullable|string|max:50',
                'nomineeAddress' => 'nullable|string|max:500',
                'nomineeComments' => 'nullable|string|max:500',
                'nomineeImage' => 'nullable',
                'nomineeSignature' => 'nullable',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors(),
                ], 422);
            }


            $data = $request->except(['member_profiles', 'member_signature', 'nomineeImage', 'nomineeSignature']);

            if ($request->hasFile('member_profiles')) {
                $image = $request->file('member_profiles');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('member/memberProfile'), $imageName);
                // $imageName = 'employee/profile'. $imageName;
                $data['member_profiles'] = 'member/memberProfile/' . $imageName;
            }

            if ($request->hasFile('member_signature')) {
                $image = $request->file('member_signature');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('member/membersignature'), $imageName);
                // $imageName = 'employee/profile'. $imageName;
                $data['member_signature'] = 'member/membersignature/' . $imageName;
            }

            if ($request->hasFile('nomineeImage')) {
                $image = $request->file('nomineeImage');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('member/nominee'), $imageName);
                // $imageName = 'employee/profile'. $imageName;
                $data['nomineeImage'] = 'member/nominee/' . $imageName;
            }

            if ($request->hasFile('nomineeSignature')) {
                $image = $request->file('nomineeSignature');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('member/nomieSignature'), $imageName);
                // $imageName = 'employee/profile'. $imageName;
                $data['nomineeSignature'] = 'member/nomieSignature/' . $imageName;
            }
            $data['member_card_id'] = $random_id;

            $member = MemberManage::create($data);

            return response()->json([
                'message' => 'Data created successfully!!',
                'data' => $member,
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthorized Access'
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
        if (Auth::check()) {

            $member = MemberManage::find($id);


            $request->merge([
                'openingDate' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->openingDate)->format('Y-m-d'),
                'dataOfBirth' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->dataOfBirth)->format('Y-m-d'),
            ]);


            $validator = Validator::make($request->all(), [
                'memberName_english' => 'required|string',
                'memberName_bangla' => 'required|string',
                'banchGroup_id' => 'nullable|exists:branch_groups,id',
                'phoneNumber' => 'required|string',
                'fatherName' => 'required|string',
                'motherName' => 'required|string',
                'spouseName' => 'nullable|string',
                'openingDate' => 'required|date',
                'refferedBy' => 'nullable|string',
                'nationaId' => 'nullable|string',
                'birthCertificate' => 'nullable|string',
                'email' => 'nullable|email',
                'bloodGroup_Id' => 'nullable|exists:blood_groups,id',
                'gender_Id' => 'required|exists:genders,id',
                'religion_id' => 'nullable|exists:riligions,id',
                'maritalStatus_id' => 'nullable|exists:marital_statuses,id',
                'dataOfBirth' => 'nullable|date',
                'present_address' => 'required|string|max:500',
                'permanent_address' => 'required|string|max:500',
                'monthlyIncome' => 'required|string|max:100',
                'education_id' => 'nullable|exists:education,id',
                'profession' => 'required|string|max:255',
                'admissionFees' => 'nullable|numeric',
                'otherFees' => 'nullable|numeric',
                'member_profiles' => 'nullable',
                'member_signature' => 'nullable',
                'nomineeName' => 'required|string|max:255',
                'nomineeFather' => 'nullable|string|max:255',
                'nomineeMother' => 'nullable|string|max:255',
                'nomineePhone' => 'nullable|string|max:15',
                'nomineeRelation' => 'nullable|string|max:255',
                'nomineeNationId' => 'nullable|string|max:50',
                'nomineeAddress' => 'nullable|string|max:500',
                'nomineeComments' => 'nullable|string|max:500',
                'nomineeImage' => 'nullable',
                'nomineeSignature' => 'nullable',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $request->except(['member_profiles', 'member_signature', 'nomineeImage', 'nomineeSignature']);

            // Handle profile photo update
            if ($request->hasFile('member_profiles')) {

                if ($member->member_profiles && file_exists(public_path($member->member_profiles))) {
                    unlink(public_path($member->member_profiles));
                }

                if ($request->hasFile('member_profiles')) {
                    $image = $request->file('member_profiles');
                    $imageName = time() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('member/memberProfile'), $imageName);
                    $data['member_profiles'] = 'member/memberProfile/' . $imageName;
                }
            }


            if ($request->hasFile('member_signature')) {

                if ($member->member_signature && file_exists(public_path($member->member_signature))) {
                    unlink(public_path($member->member_signature));
                }

                if ($request->hasFile('member_signature')) {
                    $image = $request->file('member_signature');
                    $imageName = time() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('member/membersignature'), $imageName);
                    // $imageName = 'employee/profile'. $imageName;
                    $data['member_signature'] = 'member/membersignature/' . $imageName;
                }



                if ($request->hasFile('nomineeImage')) {

                    if ($member->nomineeImage && file_exists(public_path($member->nomineeImage))) {
                        unlink(public_path($member->nomineeImage));
                    }

                    if ($request->hasFile('nomineeImage')) {
                        $image = $request->file('nomineeImage');
                        $imageName = time() . '.' . $image->getClientOriginalExtension();
                        $image->move(public_path('member/nominee'), $imageName);
                        // $imageName = 'employee/profile'. $imageName;
                        $data['nomineeImage'] = 'member/nominee/' . $imageName;
                    }
                }



                if ($request->hasFile('nomineeSignature')) {

                    if ($member->nomineeSignature && file_exists(public_path($member->nomineeSignature))) {
                        unlink(public_path($member->nomineeSignature));
                    }

                    if ($request->hasFile('nomineeSignature')) {
                        $image = $request->file('nomineeSignature');
                        $imageName = time() . '.' . $image->getClientOriginalExtension();
                        $image->move(public_path('member/nomieSignature'), $imageName);
                        // $imageName = 'employee/profile'. $imageName;
                        $data['nomineeSignature'] = 'member/nomieSignature/' . $imageName;
                    }
                }


                $member->update($data);

                return response()->json([
                    'message' => 'Data updated successfully!',
                    'data' => $member,
                ]);

            }
        } else {
            return response()->json([
                'error' => 'Unauthorized Access'
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        if (Auth::check()) {

            $member = MemberManage::find($id);

            if ($member->member_profiles && file_exists(public_path($member->member_profiles))) {
                unlink(public_path($member->member_profiles));
            }

            if ($member->member_signature && file_exists(public_path($member->member_signature))) {
                unlink(public_path($member->member_signature));
            }

            if ($member->nomineeImage && file_exists(public_path($member->nomineeImage))) {
                unlink(public_path($member->nomineeImage));
            }

            if ($member->nomineeSignature && file_exists(public_path($member->nomineeSignature))) {
                unlink(public_path($member->nomineeSignature));
            }


            if ($member) {
                $member->delete();
                return response()->json([
                    'message' => 'Data deleted successfully!!',
                ]);
            } else {
                return response()->json([
                    'message' => 'Data not found!!',
                ], 404);
            }
        } else {
            return response()->json([
                'error' => 'Unauthorized Access'
            ], 400);
        }
    }



    public function searchMember(Request $request)
    {
        if (Auth::check()) {

            $search = $request->input('search');

            $data = MemberManage::with('branchGroup')
                ->where('memberName_english', 'LIKE', '%' . $search . '%')
                ->where('phoneNumber', 'LIKE', '%' . $search . '%')
                ->get();

            return response()->json([
                'message' => 'Data get successfully',
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthorized Access'
            ], 400);
        }
    }

    public function getMemberByBranch(Request $request)
    {


        if (Auth::check()) {

            $branchId = $request->input('branch_manage_id');

            $employees = MemberManage::with('branchGroup')->where('banchGroup_id', $branchId)->get();

            return response()->json([
                'message' => 'Employees',
                'data' => $employees,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
    }


    public function memberShow(){
        if(Auth::check()){
            $members = MemberManage::all();
            return response()->json([
               'message' => 'Data get successfully',
                'data' => $members,
            ]);

        }
        else{
            return response()->json([
               'message' => 'Unauthorized Access'
            ], 400);
        }
    }



    public function closeMember(Request $request){
        if(Auth::check()){

            $id = $request->id;
            $reason = $request->close_reason;

            $member = MemberManage::find($id);
            $member->status = '0';
            $member->closing_reason = $reason;
            $member->save();
            return response()->json([
               'message' => 'Member closed successfully',
                'data' => $member,
            ]);



        }
        else{
            return response()->json([
               'message' => 'Unauthorized Access'
            ], 400);
        }
    }



    public function transferGroup(Request $request){
        if(Auth::check()){
            $id = $request->member_id;
            $transfer_reason = $request->transfer_reason;
            $branch_group_id = $request->branch_group_id;
            $member = MemberManage::find($id);
            $member->banchGroup_id = $branch_group_id;
            $member->transfer_reason = $transfer_reason;
            $member->save();
            return response()->json([
               'message' => 'Member transfer successfully',
                'data' => $member,
            ]);


        }
        else{
            return response()->json([
               'message' => 'Unauthorized Access'
            ], 400);
        }
    }



    public function memberStatementSearchFilter(Request $request){
        if(Auth::check()){
            $member_id = $request->input('member_id');
            $branch_id = $request->input('branch_id');
            $installment_date = $request->input('installment_date');
            $updated_at = $request->input('updated_at');


            // Cash Sales Filter


            $cashSalesQuery = CashSale::with([
                // 'cashDetails',
                'cashPayments',
                'cashPayments.paymentMethod',
                'cashDetails.product',
                'cashDetails.product.productCategory',
                'cashDetails.product.ProductBrand',
                'member',
                'member.branchGroup',
                'member.branchGroup.employee',
                'member.branchGroup.employee.branchName',

            ]);

            if ($installment_date && $updated_at) {
                $cashSalesQuery->whereBetween('invoice_date', [$installment_date, $updated_at]);
            }

            if ($member_id) {
                $cashSalesQuery->where('member_id', $member_id);
            }

            if ($branch_id) {
                $cashSalesQuery->whereHas('member.branchGroup.employee.branchName', function ($query) use ($branch_id) {
                    $query->where('id', $branch_id);
                });
            }

            // Fetch results
            $cashSales = $cashSalesQuery->paginate(10);


            // Hire Sales Filter

            $hireSalesQuery = HireProductSale::with([

                // 'hireProductSaleDetail',
                'hirePayments',
                'hirePayments.paymentMethod',
                'hireProductSaleDetail.product',
                'hireProductSaleDetail.product.productCategory',
                'hireProductSaleDetail.product.ProductBrand',

                'member',
                'member.branchGroup',
                'member.branchGroup.employee',
                'member.branchGroup.employee.branchName',
            ]);

            if ($installment_date && $updated_at) {
                $hireSalesQuery->whereBetween('invoice_date', [$installment_date, $updated_at]);
            }

            if ($member_id) {
                $hireSalesQuery->where('member_id', $member_id);
            }



            if ($branch_id) {
                $hireSalesQuery->whereHas('member.branchGroup.employee.branchName', function ($query) use ($branch_id) {
                    $query->where('id', $branch_id);
                });
            }



            $hireSales = $hireSalesQuery->paginate(10);



            // Installment Filter

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

            if ($member_id) {
                $installmentQuery->where('member_id', $member_id);
            }

            if ($branch_id) {
                $installmentQuery->whereHas('member.branchGroup.employee.branchName', function ($query) use ($branch_id) {
                    $query->where('id', $branch_id);
                });
            }


            $installmentDetails = $installmentQuery->paginate(10);




            return response()->json([
                'cashDetails' => $cashSales,
                'hireDetails' => $hireSales,
                'installmentDetails' => $installmentDetails
            ]);



        }
        else{
            return response()->json([
               'message' => 'Unauthorized Access'
            ], 400);
        }

    }

    public function eyeViewDetails($member_id){
        if(Auth::check()){
            $member = MemberManage::with([

                'bloodGroup',
                'branchGroup',
                'branchGroup.country',
                'branchGroup.division',
                'branchGroup.district',
                'branchGroup.member',
                'gender',
                'religion',
                'education',
                'meritalStatus'
            ])
                ->where('id', $member_id)
                ->first();

            return response()->json([
               'message' => 'Data get successfully',
                'data' => $member,
            ]);

        }
        else{
            return response()->json([
               'message' => 'Unauthorized Access'
            ], 400);
        }

    }



















}


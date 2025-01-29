<?php

namespace App\Http\Controllers\CompanySettings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CompanySettingController extends Controller
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
        if(Auth::check()){
            $validator = Validator::make($request->all(),[
                'company_name' => 'required|string',
            'company_email' => 'required|email',
            'company_phone' => 'required|string',
            'company_telephone' => 'nullable|string',
            'company_address' => 'required|string',
            'company_print_logo' => 'required',
            'company_logo' => 'required',
            'language_id' => 'required|exists:languages,id',

            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ]);
            }


            $data = $request->except(['company_print_logo', 'company_logo']);

            if ($request->hasFile('company_print_logo')) {
                $image = $request->file('company_print_logo');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('company/printLogo'), $imageName);
                $data['company_print_logo'] = 'company/printLogo/' . $imageName;
            }

            if ($request->hasFile('company_logo')) {
                $image = $request->file('company_logo');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('company/logo'), $imageName);
                $data['company_logo'] = 'company/logo/' . $imageName;
            }

            $member = CompanySetting::create($data);
            return response()->json([
                'message' =>'Successfully created company',
                 'data'=>$member
                ]);

        }
        else{
            return response()->json([
                'errors' => 'Unauthorized',
            ]);
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

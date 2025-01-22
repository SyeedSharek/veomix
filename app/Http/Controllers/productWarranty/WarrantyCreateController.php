<?php

namespace App\Http\Controllers\productWarranty;

use App\Http\Controllers\Controller;
use App\Models\MemberManage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarrantyCreateController extends Controller
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
        //
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

    public function memberDetails($member_Id){
        if(Auth::check()){
            $member = MemberManage::find($member_Id);

            if($member){
                return response()->json([
                    'data' => $member,
                ],200);
            }
            else{
                return response()->json([
                   'message' => 'Member not found',
                ],404);
            }

        }
        else{
            return response()->json([
                'message' => 'Unauthorized Access',
            ],400);
        }

    }



}

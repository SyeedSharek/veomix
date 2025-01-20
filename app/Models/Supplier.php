<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['id','supplierName','proprieTorModel','phoneNumber','contactPersonName','openDate',
    'email','webAddress','supplierGradeId','supplierAddress','branchId','supplierId'];

    public function supplierGrade(){
        return $this->belongsTo(SupplierGrade::class, 'supplierGradeId');
    }

    public function branch(){
        return $this->belongsTo(BranchManage::class, 'branchId');
    }





}

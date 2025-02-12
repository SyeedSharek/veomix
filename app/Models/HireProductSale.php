<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HireProductSale extends Model
{
    protected $guarded = [];

    public function member(){
        return $this->belongsTo(MemberManage::class,'member_id');
    }

    public function hireProductSaleDetail(){
        return $this->hasMany(HireProductDetails::class,'hire_product_sales_id');

    }

    public function hirePayments(){
        return $this->hasMany(HireProductPayment::class,'hire_product_sales_id');
    }


}

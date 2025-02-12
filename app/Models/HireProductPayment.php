<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HireProductPayment extends Model
{
    protected $guarded = [];


    public function hireProductSale(){
        return $this->hasMany(HireProductSale::class,'hire_product_sales_id');
    }

    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class,'payment_type_id');
    }









}

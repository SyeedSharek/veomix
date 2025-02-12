<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholeProductSale extends Model
{
    protected $guarded = [];

    public function wholeSalier(){
        return $this->belongsTo(WholeSale::class, 'whole_salier_member_id', 'id');
    }


    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class,'payment_type_id');
    }

    public function wholeProductSalesDetail(){
        return $this->hasMany(WholeProductSalesDetail::class,'whole_product_sales_id');
    }

    public function wholePayment(){
        return $this->hasMany(WholeProductSalePayment::class,'whole_product_sales_id');
    }





}

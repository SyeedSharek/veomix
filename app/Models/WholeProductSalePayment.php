<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholeProductSalePayment extends Model
{
    protected $guarded = [];

    public function wholeProductSale(){
        return $this->belongsTo(WholeProductSale::class,'whole_product_sale_id');
    }

    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class,'payment_type_id');
    }

}

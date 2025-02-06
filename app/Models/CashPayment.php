<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashPayment extends Model
{
    protected $guarded = [];

    public function cashPayment(){

        return $this->belongsTo(CashSale::class,'cash_id');
    }

    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class,'payment_type_id');
    }


}

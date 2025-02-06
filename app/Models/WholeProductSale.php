<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholeProductSale extends Model
{
    protected $guarded = [];

    public function wholeSalier(){
        return $this->belongsTo(WholeSale::class,'whole_saler_id');
    }

    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class,'payment_type_id');
    }



}

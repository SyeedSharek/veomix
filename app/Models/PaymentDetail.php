<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    protected $guarded = [];

    public function billing(){
        return $this->belongsTo(Billing::class,'billing_id');
    }


}

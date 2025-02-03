<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingDetail extends Model
{
    protected $guarded = [];

    public  function billing(){
        return $this->belongsTo(Billing::class,'billing_id');
    }

   public function product(){
     return $this->belongsTo(Product::class,'product_id');
   }




}

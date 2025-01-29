<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasePaymentDetails extends Model
{
    protected $fillable = ['payment_method_id','billing_id','purchase_date','product_warranty_date'];
}

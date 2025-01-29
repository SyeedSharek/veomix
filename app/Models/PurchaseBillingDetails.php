<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseBillingDetails extends Model
{
    protected $fillable = ['supplier_id','product_id','total_bill_amount','supplier_id','invoice_amount','product_quantity','after_discount_total_amount','customer_balance','customer_due_balance'];
}

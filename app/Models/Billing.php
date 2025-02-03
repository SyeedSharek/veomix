<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $guarded = [];




    public function billingDetails()
    {
        return $this->hasMany(BillingDetail::class, 'billing_id');
    }

    // In the Billing model
public function supplier()
{
    return $this->belongsTo(Supplier::class, 'supplier_id'); // Adjust the 'supplier_id' if necessary
}

}

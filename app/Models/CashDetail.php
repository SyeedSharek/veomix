<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashDetail extends Model
{
    protected $guarded = [];

    public function cashSales(){
        return $this->belongsToMany(CashSale::class, 'cash_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }
}

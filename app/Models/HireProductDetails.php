<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HireProductDetails extends Model
{
    protected $guarded = [];

    public function hireProductSale(){
        return $this->belongsTo(HireProductSale::class,'hire_product_sales_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }




}

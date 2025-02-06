<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholeProductSalesDetail extends Model
{
    protected $guarded = [];

    public function wholeProductSale(){
        return $this->belongsTo(WholeProductSale::class,'whole_product_sale_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }


}

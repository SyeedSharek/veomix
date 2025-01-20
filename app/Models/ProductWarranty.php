<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductWarranty extends Model
{
    protected $fillable = [
        'category_id',
        'brand_id',
        'product_id',
        'model',
        'productCoverage',
        'warrantyLimitation',
        'message',
    ];

    public function category(){
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    public function brand(){
        return $this->belongsTo(ProductBrand::class, 'brand_id');
    }
    public function product(){
        return $this->belongsTo(Product::class,  'product_id');
    }
}

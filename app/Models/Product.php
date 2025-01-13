<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable =[
        'productName' ,
                'productModel',
                'category_id' ,
                'brand_id' ,
                'purchase_price',
                'sales_price' ,
                'wholeSale_price' ,
                'tax_rate' ,
                'loan_price' ,
                'discountType_id',
                'discount_percentage' ,
                'discountAmount' ,
                'discountFormDate' ,
                'discountUpToDate' ,
                'productType' ,
                'productHighLight' ,
                'productDescription',
    ];

    public function productCategory(){
        return $this->belongsTo(ProductCategory::class,'category_id');
    }

    public function productBrand(){
        return $this->belongsTo(ProductBrand::class,'brand_id');
    }
    public function productDiscountType(){
        return $this->belongsTo(DiscountType::class,'discountType_id');
    }

}

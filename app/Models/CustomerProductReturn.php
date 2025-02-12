<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProductReturn extends Model
{
    protected $guarded = [];

    public function member(){
        return $this->belongsTo(MemberManage::class,'member_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }
}

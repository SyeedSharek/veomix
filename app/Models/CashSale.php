<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashSale extends Model
{
    protected $guarded = [];
    public function memeber(){
        return $this->belongsTo(MemberManage::class,'member_id');
    }
}

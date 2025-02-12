<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashSale extends Model
{
    protected $guarded = [];
    public function member(){
        return $this->belongsTo(MemberManage::class,'member_id');
    }

    public function cashDetails()
    {
        return $this->hasMany(CashDetail::class, 'cash_id');
    }

    public function cashPayments()
    {
        return $this->hasMany(CashPayment::class, 'cash_id');
    }

}

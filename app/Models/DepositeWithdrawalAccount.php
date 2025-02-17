<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositeWithdrawalAccount extends Model
{
    protected $guarded = [];

    public function member(){
        return $this->belongsTo(MemberManage::class,'member_id');
    }


}

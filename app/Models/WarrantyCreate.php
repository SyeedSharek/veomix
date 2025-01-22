<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantyCreate extends Model
{



    public function member(){
        return $this->belongsTo(MemberManage::class);
    }
}

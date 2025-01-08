<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchManage extends Model
{
    protected $guarded = [];

    public function country(){
        return $this->belongsTo(Country::class);
    }
    public function division(){
        return $this->belongsTo(Division::class);
    }
    public function district(){
        return $this->belongsTo(District::class);
    }
    public function Region(){
        return $this->belongsTo(Region::class);
    }


}

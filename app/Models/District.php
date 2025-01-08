<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{

    protected $guarded = [];

    public function country(){
        return $this->belongsTo(Country::class);
    }

    public function division(){
        return $this->belongsTo(Division::class);
    }

    public function upazilas(){
        return $this->hasMany(Upazila::class);
    }

    public function unions(){
        return $this->hasMany(Union::class);
    }


}

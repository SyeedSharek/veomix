<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{

    protected $guarded = [];

    public function country(){
        return $this->belongsTo(Country::class);
    }

    public function districts(){
        return $this->hasMany(District::class);
    }

    public function upazilas(){
        return $this->hasMany(Upazila::class);
    }

    public function unions(){
        return $this->hasMany(Union::class);
    }

    public function rigion(){
        return $this->belongsTo(Region::class);
    }


}

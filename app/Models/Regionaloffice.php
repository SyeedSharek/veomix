<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Regionaloffice extends Model
{

    protected $guarded = [];

    public function divisionOffice(){
        return $this->belongsTo(DivisionOffice::class,'divisionoffice_id','id');
    }

    public function country(){
        return $this->belongsTo(Country::class);
    }
    public function district(){
        return $this->belongsTo(District::class);
    }
    public function region(){
        return $this->belongsTo(Region::class,'regional_id','id');
    }

    public function employees()
        {
            return $this->hasMany(Employee::class, 'divisionoffice_id', 'id');
        }



}

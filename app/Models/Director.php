<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Director extends Model
{
    protected $guarded =[];

    public function education(){
        return $this->belongsTo(Education::class,'education_id');
    }

    public function bloodGroup(){
        return $this->belongsTo(BloodGroup::class,'blood_group_id');
    }

    public function religion(){
        return $this->belongsTo(Riligion::class,'religion_id');
    }

    public function gender(){
        return $this->belongsTo(Gender::class,'gender_id');
    }
    public function maritalStatus(){
        return $this->belongsTo(MaritalStatus::class,'marital_id');
    }

    public function designation(){
        return $this->belongsTo(Designation::class,'designation_id');
    }


}

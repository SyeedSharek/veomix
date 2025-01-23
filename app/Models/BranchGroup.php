<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchGroup extends Model
{
    protected $fillable =['group_name','employee_id','member_id','openDate','country_id','division_id','distric_id','upozila','union','villageName','address','status'];



    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function member(){
        return $this->belongsTo(Employee::class,'member_id');
    }



    public function branch_groups()
    {
        return $this->hasMany(Employee::class); // Or another appropriate relationship type
    }


    public function country(){
        return $this->belongsTo(Country::class,'country_id');
    }
    public function division(){
        return $this->belongsTo(Division::class,'division_id');
    }
    public function district(){
        return $this->belongsTo(District::class,'distric_id');
    }
}

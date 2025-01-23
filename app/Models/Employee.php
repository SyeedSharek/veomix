<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = [];

    protected $appends = ['profile_photo_url', 'signature_photo_url'];


    public function ManagerName()
    {
        return $this->belongsTo(Employee::class, 'designation_id');
    }


    // Accessor for profilePhoto URL
    public function getProfilePhotoUrlAttribute()
    {
        return asset($this->profilePhoto);
    }

    // Accessor for signaturePhoto URL
    public function getSignaturePhotoUrlAttribute()
    {
        return asset($this->signaturePhoto);
    }



    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function branchName()
    {
        return $this->belongsTo(BranchManage::class, 'branch_manage_id');
    }

    public function maritalStatus(){
        return $this->belongsTo(MaritalStatus::class, 'marital_id');
    }
    public function gender(){
        return $this->belongsTo(Gender::class, 'gender_id');
    }
    public function religion(){
        return $this->belongsTo(Riligion::class, 'riligion_id');
    }

    public function education(){
        return $this->belongsTo(Education::class, 'education_id');
    }
    public function bloodGroup(){
        return $this->belongsTo(BloodGroup::class, 'blood_id');
    }









}

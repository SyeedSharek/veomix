<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberManage extends Model
{
    protected $guarded = [];

    protected $appends = ['member_profiles_url', 'member_signature_url', 'nomineeImage_url', 'nomineeSignature_url'];

    public function bloodGroup()
    {
        return $this->belongsTo(BloodGroup::class, 'bloodGroup_Id', 'id');
    }

    // public function branchGroup()
    // {
    //     return $this->belongsTo(BranchManage::class, 'branchGroup_id', 'id');
    // }

    public function branchGroup()
    {
        return $this->belongsTo(BranchGroup::class, 'banchGroup_id', 'id');
    }





    public function gender(){
        return $this->belongsTo(Gender::class, 'gender_Id', 'id');
    }

    public function religion(){
        return $this->belongsTo(Riligion::class, 'religion_id', 'id');
    }

    public function education(){
        return $this->belongsTo(Education::class, 'education_id', 'id');
    }

    public function meritalStatus(){
        return $this->belongsTo(MaritalStatus::class, 'education_id', 'id');
    }






    public function getMemberProfilesUrlAttribute()
    {
        return url($this->member_profiles);
    }

    public function getMemberSignatureUrlAttribute()
    {
        return url($this->member_signature);
    }

    // Corrected accessor for nomineeImage_url
    public function getNomineeImageUrlAttribute()
    {
        return url($this->nomineeImage);
    }

    // Corrected accessor for nomineeSignature_url
    public function getNomineeSignatureUrlAttribute()
    {
        return url($this->nomineeSignature);
    }
}

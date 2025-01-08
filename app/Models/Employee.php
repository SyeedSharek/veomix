<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = [];

    protected $appends = ['profile_photo_url', 'signature_photo_url'];

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






}

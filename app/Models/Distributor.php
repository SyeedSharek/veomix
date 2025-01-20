<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distributor extends Model
{
    protected $fillable = ['distributorName','distributorId','proprietorName','phoneNumber','contactPersonName','openDate','email','webAddress','distributorGrade','distributorAddress'];


    public function distributorGrade(){
        return $this->belongsTo(DistributorGrade::class, 'distributorGrade');
    }

}

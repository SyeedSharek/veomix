<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholeSale extends Model
{
    protected $fillable = ['id','clientName','proprietorName',
    'contactPersonName','openDate','clientId','phoneNumber',
    'email','webAddress','clientGrade_Id',
    'clientAddress'];

    public function clientGrade(){
        return $this->belongsTo(ClientGrade::class, 'clientGrade_Id');
    }

}


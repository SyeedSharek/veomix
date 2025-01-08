<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Divisionoffice extends Model
{

    protected $guarded = [];

    public function division(){
        return $this->belongsTo(Division::class);
    }

}

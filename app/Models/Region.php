<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Country;
use App\Models\Division;

class Region extends Model
{
    protected $fillable = ['id','name','country_id','division_id'];
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }
}

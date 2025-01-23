<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Divisionoffice extends Model
{

    protected $guarded = [];



    public function managerName()
    {
        return $this->belongsTo(Employee::class, 'employe_Id', 'id');
    }


    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employe_Id');
    }


    public function division()
    {
        return $this->belongsTo(Division::class);
    }



    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}

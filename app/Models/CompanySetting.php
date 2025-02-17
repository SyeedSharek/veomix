<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $guarded = [];

    protected $appends = ['companey_logo_url', 'companey_print_logo_url'];

    public function language(){
        return $this->belongsTo(Language::class,'language_id');
    }

    public function designation(){
        return $this->belongsTo(Designation::class,'designation_id');
    }

    public function employee(){
        return $this->belongsTo(Employee::class,'employee_id');
    }

    // Companey Logo Show
    public function getCompaneyLogoUrlAttribute()
    {
        return asset($this->company_logo); // Converts relative path to full URL
    }

    // Accessor for company print logo URL
    public function getCompaneyPrintLogoUrlAttribute()
    {
        return asset($this->company_print_logo);
    }
}

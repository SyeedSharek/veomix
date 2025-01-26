<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeave extends Model
{
    protected $fillable = ['employee_id', 'leave_days', 'total_leave','leave_reason', 'leave_start_date', 'leave_end_date',];


    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
}

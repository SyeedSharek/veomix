<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeave extends Model
{
    protected $fillable = ['employee_id','leave_days','leave_reason','leave_start_date','leave_end_date',];
}

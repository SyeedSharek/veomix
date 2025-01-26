<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryDisbursement extends Model
{
    protected $fillable = [
        'employee_id','month_id','basicSalary','houseRent','entry_by','ta','da','festivalBonus','providentFund','salaryFromDate','salaryPayDate','totalSalary'
    ];

    public function employee(){
        return $this->belongsTo(Employee::class,'employee_id');
    }
    public function month(){
        return $this->belongsTo(Month::class,'month_id');
    }

    // In SalaryDisbursement model
    public function branch()
    {
        return $this->belongsTo(BranchManage::class, 'branch_manage_id', 'id');
    }








}

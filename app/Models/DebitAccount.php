<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebitAccount extends Model
{
    protected $guarded = [];



    public function ledgerTransaction() {
        return $this->belongsTo(LedgerTransaction::class, 'ledger_transaction_id');
    }

    public function member()
    {

        return $this->belongsTo(MemberManage::class, 'member_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function wholesaler()
    {
        return $this->belongsTo(WholeSale::class, 'whole_saler_id');
    }



    public function report_transaction(){
        return $this->belongsTo(AccountReportingType::class,'AccountReportingType');
    }
}

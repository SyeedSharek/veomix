<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerTransaction extends Model
{
    protected $guarded = [];

    public function accountTransactionType(){
        return $this->belongsTo(AccountTransactionType::class,'transaction_type_id');
    }

    public function accountTransactionLedger(){
        return $this->belongsTo(AccountLedger::class,'transaction_ledger_id');
    }

    public function debitAccount(){
        return $this->hasMany(DebitAccount::class,'ledger_transaction_id');
    }

    public function creditAccount(){
        return $this->hasMany(CreditAccount::class,'ledger_transaction_id');
    }
}

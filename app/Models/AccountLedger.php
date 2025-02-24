<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountLedger extends Model
{
    protected $guarded = [];

    public function ledgerTransaction(){
        return $this->hasMany(LedgerTransaction::class);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deposite_withdrawal_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->string('deposite_total_amount');
            $table->string('withdrawal_total_amount');
            $table->string('total_due_balance');
            $table->string('total_deposite_installment');
            $table->string('total_withdrawal_installment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposite_withdrawal_accounts');
    }
};

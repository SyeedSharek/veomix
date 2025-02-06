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
        Schema::create('hire_loan_management', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->string('invoice_number');
            $table->unsignedBigInteger('installment_type');
            $table->string('paid_installment');
            $table->string('form_fee')->nullable();
            $table->date('installment_date');
            $table->date('installment_expired_date');
            $table->string('penalty_amount')->nullable();
            $table->string('total_due')->nullable();
            $table->string('loan_paid')->nullable();
            $table->string('total_loan_due')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hire_loan_management');
    }
};

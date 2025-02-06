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
        Schema::create('hire_product_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hire_product_sales_id');
            $table->string('member_paid_amount');
            $table->unsignedBigInteger('payment_type');
            $table->string('total_amount');
            $table->string('invoice_discount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hire_product_payments');
    }
};

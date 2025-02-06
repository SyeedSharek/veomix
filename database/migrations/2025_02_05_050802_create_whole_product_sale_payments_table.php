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
        Schema::create('whole_product_sale_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('whole_product_sale_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->string('whole_paid_ammount');
            $table->string('total_amount');
            $table->string('invoice_discount');
            $table->string('after_invoice_discount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whole_product_sale_payments');
    }
};

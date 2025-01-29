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
        Schema::create('purchase_billing_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->json('product_id');
            $table->string('total_bill_amount');
            $table->string('invoice_amount');
            $table->string('product_quantity');
            $table->string('after_discount_total_amount');
            $table->string('customer_balance');
            $table->string('customer_due_balance');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_billing_details');
    }
};

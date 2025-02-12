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
        Schema::create('customer_product_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('product_id');
            $table->string('return_product_quantity');
            $table->date('purchase_date');
            $table->unsignedBigInteger('sales_type_id');
            $table->string('invoice_number');
            $table->date('return_date');
            $table->string('return_reason');
            $table->string('return_amopunt');
            $table->tinyInteger('status')->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_product_returns');
    }
};

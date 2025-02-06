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
        Schema::create('hire_product_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hire_product_sales_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_quantity');
            $table->string('product_price');
            $table->string('product_discount_percentage');
            $table->string('subtotal');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hire_product_details');
    }
};

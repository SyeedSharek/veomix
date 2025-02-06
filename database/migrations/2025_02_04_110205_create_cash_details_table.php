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
        Schema::create('cash_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cash_id');
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
        Schema::dropIfExists('cash_details');
    }
};

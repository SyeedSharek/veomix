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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('productName');
            $table->string('productModel');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('brand_id');
            $table->string('purchase_price');
            $table->string('sales_price');
            $table->string('wholeSale_price');
            $table->string('tax_rate');
            $table->string('loan_price');
            $table->unsignedBigInteger('discountType_id');
            $table->string('discount_percentage');
            $table->string('discountAmount');
            $table->date('discountFormDate');
            $table->date('discountUpToDate');
            $table->string('productType');
            $table->text('productHighLight');
            $table->text('productDescription');
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('product_brands')->onDelete('cascade');
            $table->foreign('discountType_id')->references('id')->on('discount_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

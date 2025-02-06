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
        Schema::create('hire_product_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->date('invoice_warranty');
            $table->string('tax_amount');
            $table->string('total_quantity');
            $table->string('total_amount');
            $table->string('entry_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hire_product_sales');
    }
};

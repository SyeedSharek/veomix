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
        Schema::create('whole_product_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('whole_salier_member_id');
            $table->string('invoice_date');
            $table->string('invoice_warranty');
            $table->string('tax_amount');
            $table->string('total_quantity');
            $table->string('entry_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whole_product_sales');
    }
};

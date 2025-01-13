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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplierName');
            $table->string('proprieTorModel');
            $table->string('phoneNumber');
            $table->string('contactPersonName');
            $table->date('openDate');
            $table->string('email');
            $table->string('webAddress');
            $table->unsignedBigInteger('supplierGrade');
            $table->text('supplierAddress');
            $table->unsignedBigInteger('branchId');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};

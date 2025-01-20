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
        Schema::create('distributors', function (Blueprint $table) {
            $table->id();
            $table->string('distributorName');
            $table->string('distributorId');
            $table->string('proprietorName');
            $table->string('phoneNumber');
            $table->string('contactPersonName');
            $table->date('openDate');
            $table->string('email');
            $table->string('webAddress');
            $table->unsignedBigInteger('distributorGrade');
            $table->text('distributorAddress');
            $table->timestamps();
            $table->foreign('distributorGrade')->references('id')->on('distributor_grades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributors');
    }
};

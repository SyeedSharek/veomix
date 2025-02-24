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
        Schema::create('directors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('father_name');
            $table->string('mother_name');
            $table->unsignedBigInteger('gender_id');
            $table->text('description');
            $table->date('joining_data');
            $table->text('present_address');
            $table->text('permanent_address');
            $table->date('date_of_birth');
            $table->unsignedBigInteger('national_id');
            $table->unsignedBigInteger('religion_id');
            $table->unsignedBigInteger('blood_group_id');
            $table->unsignedBigInteger('education_id');
            $table->unsignedBigInteger('marital_id');
            $table->string('email');
            $table->string('phone');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directors');
    }
};

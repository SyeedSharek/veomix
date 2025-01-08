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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employeeName');
            $table->string('employeeId');
            $table->string('fatherName');
            $table->date('joingDate');
            $table->string('managerName');
            $table->string('nationalId');
            $table->date('dateOfBirth');
            $table->unsignedBigInteger('riligion_id');
            $table->unsignedBigInteger('branch_manage_id');
            $table->unsignedBigInteger('education_id');
            $table->unsignedBigInteger('designation_id');
            $table->unsignedBigInteger('blood_id');
            $table->unsignedBigInteger('gender_id');
            $table->string('email');
            $table->unsignedBigInteger('marital_id');
            $table->text('presentAddress');
            $table->text('permanentAddress');
            $table->string('emergencyNumber');
            $table->string('phoneNumber');
            $table->string('profilePhoto');
            $table->string('signaturePhoto');
            $table->timestamps();
            $table->foreign('riligion_id')->references('id')->on('riligions')->onDelete('cascade');
            $table->foreign('branch_manage_id')->references('id')->on('branch_manages')->onDelete('cascade');
            $table->foreign('education_id')->references('id')->on('education')->onDelete('cascade');
            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('cascade');
            $table->foreign('blood_id')->references('id')->on('blood_groups')->onDelete('cascade');
            $table->foreign('gender_id')->references('id')->on('genders')->onDelete('cascade');
            $table->foreign('marital_id')->references('id')->on('marital_statuses')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

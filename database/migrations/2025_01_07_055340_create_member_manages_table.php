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
        Schema::create('member_manages', function (Blueprint $table) {
            $table->id();
            $table->string('memberName_english');
            $table->string('memberName_bangla');
            $table->unsignedBigInteger('banchGroup_id')->nullable();
            $table->string('phoneNumber')->nullable();
            $table->string('fatherName');
            $table->string('motherName');
            $table->string('spouseName')->nullable();
            $table->date('openingDate')->nullable();
            $table->string('refferedBy')->nullable();
            $table->string('nationaId')->nullable();
            $table->string('birthCertificate')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('bloodGroup_Id')->nullable();
            $table->unsignedBigInteger('gender_Id')->nullable();
            $table->unsignedBigInteger('religion_id')->nullable();
            $table->unsignedBigInteger('maritalStatus_id')->nullable();
            $table->string('dataOfBirth')->nullable();
            $table->string('present_address');
            $table->string('permanent_address');
            $table->string('monthlyIncome');
            $table->unsignedBigInteger('education_id')->nullable();
            $table->string('profession');
            $table->string('admissionFees')->nullable();
            $table->string('otherFees')->nullable();
            $table->string('member_profiles')->nullable();
            $table->string('member_signature')->nullable();
            $table->string('nomineeName');
            $table->string('nomineeFather')->nullable();
            $table->string('nomineeMother')->nullable();
            $table->string('nomineePhone')->nullable();
            $table->string('nomineeRelation')->nullable();
            $table->string('nomineeNationId')->nullable();
            $table->string('nomineeAddress')->nullable();
            $table->string('nomineeComments')->nullable();
            $table->string('nomineeImage')->nullable();
            $table->string('nomineeSignature')->nullable();
            $table->timestamps();
            $table->foreign('bloodGroup_id')->references('id')->on('blood_groups')->onDelete('cascade');
            $table->foreign('gender_Id')->references('id')->on('genders')->onDelete('cascade');
            $table->foreign('religion_id')->references('id')->on('riligions')->onDelete('cascade');
            $table->foreign('maritalStatus_id')->references('id')->on('marital_statuses')->onDelete('cascade');
            $table->foreign('education_id')->references('id')->on('education')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_manages');
    }
};

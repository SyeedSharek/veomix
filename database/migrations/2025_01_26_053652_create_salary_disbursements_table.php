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
        Schema::create('salary_disbursements', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('month_id');
            $table->string('basicSalary');
            $table->string('houseRent');
            $table->string('ta');
            $table->string('da');
            $table->string('festivalBonus');
            $table->string('providentFund');
            $table->string('TotalSalary');
            $table->date('salaryFromDate');
            $table->date('salaryPayDate');
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('month_id')->references('id')->on('months')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_disbursements');
    }
};

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
        Schema::create('regionaloffices', function (Blueprint $table) {
            $table->id();
            $table->string('office_name');
            $table->unsignedBigInteger('divisionoffice_id');
            $table->unsignedBigInteger('division_id');
            $table->date('opening_date');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('district_id');
            $table->unsignedBigInteger('upazila_id');
            $table->unsignedBigInteger('union_id');
            $table->string('employee_phone');
            $table->text('address');
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regionaloffices');
    }
};

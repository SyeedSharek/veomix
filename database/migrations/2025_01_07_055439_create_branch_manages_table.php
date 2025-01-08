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
        Schema::create('branch_manages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('region_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('openingDate');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('district_id');
            $table->string('upozila');
            $table->string('union');
            $table->string('managerPhone');
            $table->text('address');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_manages');
    }
};

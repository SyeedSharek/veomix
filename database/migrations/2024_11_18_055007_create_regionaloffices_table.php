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
            $table->string('regionalName');
            $table->unsignedBigInteger('divisionoffice_id');
            $table->date('opening_date');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('district_id');
            $table->unsignedBigInteger('regional_id');
            $table->string('upozila');
            $table->string('union_id');
            $table->text('address');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->foreign('divisionoffice_id')->references('id')->on('divisionoffices')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
            $table->foreign('regional_id')->references('id')->on('regions')->onDelete('cascade');
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

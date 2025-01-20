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
        Schema::create('whole_sales', function (Blueprint $table) {
            $table->id();
            $table->string('clientName');
            $table->string('proprietorName');
            $table->string('contactPersonName');
            $table->date('openDate');
            $table->string('email');
            $table->string('webAddress');
            $table->unsignedBigInteger('clientGrade_Id');
            $table->text('clientAddress');
            $table->timestamps();
            $table->foreign('clientGrade_Id')->references('id')->on('client_grades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whole_sales');
    }
};

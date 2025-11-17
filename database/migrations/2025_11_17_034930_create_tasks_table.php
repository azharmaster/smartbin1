<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();                     // Primary key
        $table->unsignedBigInteger('assetID'); // Foreign key to assets
        $table->unsignedBigInteger('userID');  // Foreign key to users
        $table->string('description');    // Task description
        $table->timestamps();             // created_at & updated_at

        // Foreign key constraints
        $table->foreign('assetID')->references('id')->on('assets')->onDelete('cascade');
        $table->foreign('userID')->references('id')->on('users')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

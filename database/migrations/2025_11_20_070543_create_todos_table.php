<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userID'); // who created the todo
            $table->string('todo');               // the task
            $table->enum('status', ['pending', 'done'])->default('pending'); 
            $table->timestamps();

            // assuming your user table PK = userID
            $table->foreign('userID')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
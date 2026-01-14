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
    Schema::create('events', function (Blueprint $table) {
        $table->id();
        $table->string('event_name');
        $table->string('pic_phone');
        $table->string('location');
        $table->date('start_date');
        $table->date('end_date');
        $table->time('start_time');
        $table->time('end_time');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

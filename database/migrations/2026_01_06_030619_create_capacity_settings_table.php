<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('capacity_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('empty_to'); // e.g. 39
            $table->unsignedInteger('half_to');  // e.g. 79
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('capacity_settings');
    }
};


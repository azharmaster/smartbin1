<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->foreign('device_id')
                  ->references('id_device')
                  ->on('devices')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
        });
    }
};

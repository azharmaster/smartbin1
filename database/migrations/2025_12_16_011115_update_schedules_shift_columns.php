<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->time('start_shift')->after('floor_id');
            $table->time('end_shift')->after('start_shift');
            $table->dropColumn('shift');
        });
    }

    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('shift');
            $table->dropColumn(['start_shift', 'end_shift']);
        });
    }
};

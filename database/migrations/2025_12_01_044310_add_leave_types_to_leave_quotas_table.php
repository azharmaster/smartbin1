<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('leave_quotas', function (Blueprint $table) {
            $table->integer('annual_leave')->default(0);
            $table->integer('mc')->default(0);
            $table->integer('hospitality')->default(0);
            $table->integer('emergency_leave')->default(0);
        });
    }

    public function down()
    {
        Schema::table('leave_quotas', function (Blueprint $table) {
            $table->dropColumn(['annual_leave', 'mc', 'hospitality', 'emergency_leave']);
        });
    }
};

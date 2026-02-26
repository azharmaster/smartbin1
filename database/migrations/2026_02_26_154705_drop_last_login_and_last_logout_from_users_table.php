<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login', 'last_logout']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login')->nullable();
            $table->timestamp('last_logout')->nullable();
        });
    }
};
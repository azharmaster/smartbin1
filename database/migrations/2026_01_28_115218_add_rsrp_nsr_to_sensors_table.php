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
        Schema::table('sensors', function (Blueprint $table) {
            $table->string('rsrp', 50)->nullable()->after('network');
            $table->string('nsr', 50)->nullable()->after('rsrp');
            
            // Opsional: menambahkan index
            // $table->index('rsrp');
            // $table->index('nsr');
        });
    }

    public function down()
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->dropColumn(['rsrp', 'nsr']);
        });
    }
};

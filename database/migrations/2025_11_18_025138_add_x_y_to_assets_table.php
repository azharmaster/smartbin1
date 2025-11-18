<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->decimal('x', 8, 2)->nullable()->after('category'); // adjust precision as needed
            $table->decimal('y', 8, 2)->nullable()->after('x');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['x', 'y']);
        });
    }
};


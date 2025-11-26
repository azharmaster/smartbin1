<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Add floor_id if it doesn't exist
            if (!Schema::hasColumn('tasks', 'floor_id')) {
                $table->unsignedBigInteger('floor_id')->nullable()->after('asset_id');
            }

            // Add status if it doesn't exist
            if (!Schema::hasColumn('tasks', 'status')) {
                $table->enum('status', ['pending','accepted','rejected','in_progress','done'])
                      ->default('pending')->after('description');
            }

            // Add notes if it doesn't exist
            if (!Schema::hasColumn('tasks', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'floor_id')) {
                $table->dropColumn('floor_id');
            }
            if (Schema::hasColumn('tasks', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('tasks', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};

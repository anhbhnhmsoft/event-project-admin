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
        Schema::table('event_game_gifts', function (Blueprint $table) {
            if (Schema::hasColumn('event_game_gifts', 'rate')) {
                $table->dropColumn('rate');
            };
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_game_gifts', function (Blueprint $table) {
            if (!Schema::hasColumn('event_game_gifts', 'rate')) {
                $table->integer('rate')->nullable();
            }
        });
    }
};

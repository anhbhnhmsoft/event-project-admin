<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_areas', function (Blueprint $table) {
            if (Schema::hasColumn('event_areas', 'seats_per_row')) {
                $table->dropColumn('seats_per_row')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_areas', function (Blueprint $table) {
            if (Schema::hasColumn('event_areas', 'seats_per_row')) {
                $table->dropColumn('seats_per_row');
            }
        });
    }
};

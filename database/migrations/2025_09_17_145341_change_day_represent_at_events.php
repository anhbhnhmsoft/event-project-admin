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
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'day_repersent')) {
                $table->dropColumn('day_repersent');
            }

            if (!Schema::hasColumn('events', 'day_represent')) {
                $table->dateTime('day_represent')->nullable()->comment('Ngày tổ chức sự kiện');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'day_represent')) {
                $table->dropColumn('day_represent');
            }

            if (!Schema::hasColumn('events', 'day_repersent')) {
                $table->dateTime('day_repersent')->nullable()->comment('Ngày tổ chức sự kiện');
            }
        });
    }
};

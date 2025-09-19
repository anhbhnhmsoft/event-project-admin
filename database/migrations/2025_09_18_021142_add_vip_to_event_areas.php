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
        Schema::table('event_areas', function (Blueprint $table) {
            $table->boolean('vip')->default(false);
            $table->integer('seats_per_row')->nullable()->comment('Số ghế mỗi hàng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_areas', function (Blueprint $table) {
            $table->dropColumn('vip');
            $table->dropColumn('seats_per_row');
        });
    }
};

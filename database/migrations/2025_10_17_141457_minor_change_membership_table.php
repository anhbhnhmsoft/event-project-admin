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
        Schema::table('membership', function (Blueprint $table) {
            $table->tinyInteger('type')
                ->default(1)
                ->comment('Loại gói membership, Lưu trong enum MembershipType')
                ->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};

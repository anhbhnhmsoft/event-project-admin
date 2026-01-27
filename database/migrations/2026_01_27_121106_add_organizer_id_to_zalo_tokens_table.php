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
        Schema::table('zalo_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('organizer_id')->default(1)->after('id');
            // Assuming organizers table exists, otherwise remove foreign key
            // $table->foreign('organizer_id')->references('id')->on('organizers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zalo_tokens', function (Blueprint $table) {
            $table->dropColumn('organizer_id');
        });
    }
};

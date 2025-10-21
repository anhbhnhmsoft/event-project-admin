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
        Schema::table('event_schedule_documents', function (Blueprint $table) {
            $table->unsignedInteger('price')->default('0')->comment('Giá của tài liệu private');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_schedule_documents', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};

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
        Schema::table('event_schedule_document_user', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->comment('Lưu trạng thái của khách mời đối với sự kiện');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_schedule_document_user', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

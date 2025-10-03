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
        Schema::table('user_notifications', function (Blueprint $table) {
            // Xóa foreign key trước
            $table->dropForeign(['organizer_id']);
            $table->dropForeign(['event_id']);

            // Sau đó mới drop column
            $table->dropColumn(['organizer_id', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            // Khôi phục lại cột
            $table->unsignedBigInteger('organizer_id');
            $table->unsignedBigInteger('event_id');

            // Khôi phục lại foreign key
            $table->foreign('organizer_id')->references('id')->on('organizers')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }
};

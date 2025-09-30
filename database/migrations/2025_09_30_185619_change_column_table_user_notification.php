<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('user_notifications', 'organizer_id')) {
                $table->dropConstrainedForeignId('organizer_id');
            }
            if (Schema::hasColumn('user_notifications', 'event_id')) {
                $table->dropConstrainedForeignId('event_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('user_notifications', 'organizer_id')) {
                $table->foreignId('organizer_id')->after('id')->constrained('organizers')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('user_notifications', 'event_id')) {
                $table->foreignId('event_id')->after('organizer_id')->constrained('events')->cascadeOnDelete();
            }
        });
    }
};
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
        Schema::table('event_comments', function (Blueprint $table) {
            if (Schema::hasColumn('event_comments', 'evaluation')) {
                $table->dropColumn('evaluation');
            }

            if (Schema::hasColumn('event_comments', 'is_anonymous')) {
                $table->dropColumn('is_anonymous');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_comments', function (Blueprint $table) {
            $table->string('evaluation')->nullable();
            $table->boolean('is_anonymous')->default(false);
        });
    }
};
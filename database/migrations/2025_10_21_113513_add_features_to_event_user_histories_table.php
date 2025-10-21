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
        Schema::table('event_user_histories', function (Blueprint $table) {
            $table->json('features')->nullable()->comment('Cấu hình quyền của người dùng trong sự kiện: Bình luận mất phí');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_user_histories', function (Blueprint $table) {
            $table->drop('features');
        });
    }
};

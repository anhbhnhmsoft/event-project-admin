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
        Schema::create('event_user_gift', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng event_user_gift lưu trữ kết quả nhận quà của sự kiện');
            $table->foreignId('event_game_gift_id')->constrained('event_game_gifts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_user_gift', function (Blueprint $table) {
            $table->dropIfExists('event_user_gift');
        });
    }
};

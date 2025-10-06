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
        Schema::create('event_poll_user', function (Blueprint $table) {
            $table->id();
            $table->comment('Lưu trữ danh sách User (đã Check-in) được phép tham gia một cuộc Khảo sát cụ thể.');
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('event_poll_id')
                ->constrained('event_polls')
                ->cascadeOnDelete();
            // Đảm bảo mỗi User chỉ được ghi nhận một lần cho mỗi Poll
            $table->unique(['user_id', 'event_poll_id'], 'unique_user_poll');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_poll_user');
    }
};

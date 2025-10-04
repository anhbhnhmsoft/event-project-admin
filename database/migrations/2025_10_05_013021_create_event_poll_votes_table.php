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
        Schema::create('event_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->comment('Lưu trữ phản hồi/bình chọn của người dùng đối với các câu hỏi.');
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('event_poll_question_id')
                ->constrained('event_poll_questions')
                ->cascadeOnDelete();
            $table->foreignId('event_poll_question_option_id')
                ->nullable()
                ->comment('Khóa ngoại liên kết với tùy chọn/đáp án đã chọn.')
                ->constrained('event_poll_question_options')
                ->cascadeOnDelete();
            // Ràng buộc duy nhất: Một User chỉ được trả lời một lần cho một câu hỏi (Áp dụng cho Single Choice)
            $table->unique(['user_id', 'event_poll_question_id'], 'unique_user_answer');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_poll_votes');
    }
};

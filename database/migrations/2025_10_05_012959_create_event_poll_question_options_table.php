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
        Schema::create('event_poll_question_options', function (Blueprint $table) {
            $table->id();
            $table->comment('Lưu trữ các tùy chọn/đáp án cho các câu hỏi dạng trắc nghiệm (Single/Multiple Choice).');
            $table->foreignId('event_poll_question_id')
                ->constrained('event_poll_questions')
                ->cascadeOnDelete();
            $table->char('label', length: 255)->comment('Nội dung của tùy chọn/đáp án.');
            $table->tinyInteger('order')->comment('Thứ tự hiển thị của tùy chọn.');
            // Đánh dấu đáp án đúng (Chỉ dùng cho nghiệp vụ Quiz/Thi đấu. Có thể bỏ nếu chỉ là bình chọn)
            $table->tinyInteger('is_correct')->nullable()->comment('Đánh dấu đáp án đúng (0: Sai, 1: Đúng). Chỉ áp dụng cho kiểu câu hỏi Quiz.');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_poll_question_options');
    }
};

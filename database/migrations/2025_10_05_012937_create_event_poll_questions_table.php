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
        Schema::create('event_poll_questions', function (Blueprint $table) {
            $table->id();
            $table->comment('Lưu trữ các câu hỏi thuộc về một khảo sát/bình chọn.');
            $table->foreignId('event_poll_id')
                ->constrained('event_polls')
                ->cascadeOnDelete();
            $table->tinyInteger('type')->comment('Loại câu hỏi lưu ở constant QuestionType');
            $table->text('question')->comment('Nội dung chi tiết của câu hỏi.');
            $table->tinyInteger('order')->comment('Thứ tự hiển thị của câu hỏi trong khảo sát.');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_poll_questions');
    }
};

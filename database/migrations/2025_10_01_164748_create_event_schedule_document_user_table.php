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
        Schema::create('event_schedule_document_user', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng event_schedule_document_user để lưu trữ các file trong lịch trình sự kiện người dùng từng tham gia');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_schedule_document_id')->constrained('event_schedule_documents')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('event_schedule_document_user', function (Blueprint $table) {
            $table->dropIfExists('event_schedule_document_user');
        });
    }
};

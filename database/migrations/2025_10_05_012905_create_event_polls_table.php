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
        Schema::create('event_polls', function (Blueprint $table) {
            $table->id();
            $table->comment('Lưu trữ thông tin về một đợt Khảo sát/Bình chọn cụ thể trong sự kiện.');
            $table->foreignId('event_id')
                ->comment('Khóa ngoại liên kết với bảng "events".')
                ->constrained('events')
                ->cascadeOnDelete();
            $table->text('title')->comment('Tiêu đề của cuộc khảo sát.');
            $table->timestamp('start_time')->comment('Thời điểm bắt đầu mở khảo sát.');
            $table->timestamp('end_time')->comment('Thời điểm kết thúc khảo sát.');
            $table->tinyInteger('duration_unit')->comment('Đơn vị duration lưu trữ ở constant type unit duration');
            $table->integer('duration')->comment('Thời lượng (giờ/phút/ngày) kéo dài khảo sát.');
            $table->tinyInteger('is_active')->comment('Trạng thái kích hoạt (1: Active, 0: Inactive).');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_polls');
    }
};

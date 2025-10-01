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
        Schema::create('event_game_gifts', function (Blueprint $table) {
            $table->id();
            $table->comment('Bảng event_game_gifts để lưu trữ các phần quà trong trò chơi của sự kiện');
            $table->foreignId('event_game_id')->constrained('event_games')->cascadeOnDelete();
            $table->string('name')->comment('Tên món quà');
            $table->text('description')->nullable()->comment('Mô tả món quà');
            $table->text('image')->comment('Hình ảnh món quà');
            $table->integer('quantity')->comment('Số lượng món quà');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_game_gifts');
    }
};

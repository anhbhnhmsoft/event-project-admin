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
        Schema::table('events', function (Blueprint $table) {
            $table->text('short_description')->nullable()->comment('Mô tả ngắn gọn của sự kiện');
            $table->dateTime('day_repersent')->comment('Ngày tổ chức sự kiện');
            $table->string('address')->comment('Địa chỉ sự kiện');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            //
        });
    }
};

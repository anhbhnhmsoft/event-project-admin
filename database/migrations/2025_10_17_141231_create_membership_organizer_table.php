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
        Schema::create('membership_organizer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')
                ->constrained('organizers')
                ->cascadeOnDelete();
            $table->foreignId('membership_id')
                ->constrained('membership')
                ->cascadeOnDelete();
            $table->date('start_date')->nullable()
                ->comment('Ngày bắt đầu gói membership');
            $table->date('end_date')->nullable()
                ->comment('Ngày kết thúc gói membership');
            $table->tinyInteger('status')->comment('Trạng thái gói: enum định nghĩa MembershipUserStatus');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_organizer');
    }
};

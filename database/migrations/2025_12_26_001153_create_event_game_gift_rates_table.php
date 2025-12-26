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
        Schema::create('event_game_gift_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_game_id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Null means default rate for all users');
            $table->unsignedBigInteger('event_game_gift_id');
            $table->decimal('rate', 5, 2)->default(0)->comment('Probability rate in percentage');
            $table->timestamps();

            // Foreign keys
            $table->foreign('event_game_id')->references('id')->on('event_games')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('event_game_gift_id')->references('id')->on('event_game_gifts')->onDelete('cascade');

            // Unique constraint: one rate per user-gift combination in a game
            $table->unique(['event_game_id', 'user_id', 'event_game_gift_id'], 'unique_game_user_gift_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_game_gift_rates');
    }
};

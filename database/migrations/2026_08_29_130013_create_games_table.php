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
        Schema::create('games', function (Blueprint $table) {
    $table->id();

    $table->foreignId('customer_session_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->string('game_type')->default('Snooker');
    $table->decimal('rate', 10, 2);
    $table->timestamp('played_at')->nullable();

    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};

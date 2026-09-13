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
        Schema::create('customer_sessions', function (Blueprint $table) {
    $table->id();

    $table->foreignId('customer_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->timestamp('started_at')->nullable();
    $table->timestamp('closed_at')->nullable();

    $table->decimal('total_amount', 10, 2)->default(0);
    $table->decimal('paid_amount', 10, 2)->default(0);
    $table->decimal('remaining_amount', 10, 2)->default(0);

    $table->string('status')->default('active');

    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_sessions');
    }
};

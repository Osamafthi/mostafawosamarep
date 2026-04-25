<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_persons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 190)->unique();
            $table->string('password', 255);
            $table->string('phone', 40)->nullable();

            // Admin-controlled flag. Inactive accounts cannot log in and are
            // skipped by DeliveryAssignmentService when picking the next courier.
            $table->boolean('is_active')->default(true);

            // FIFO rotation key: the active courier with the oldest value (NULL
            // sorted first) receives the next order. Bumped on every assignment.
            $table->timestamp('last_assigned_at')->nullable();

            $table->timestamps();

            // Composite index lets MySQL pick the next courier in O(log n).
            $table->index(['is_active', 'last_assigned_at'], 'idx_delivery_persons_pick');
            $table->index('email', 'idx_delivery_persons_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_persons');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('total_received');
            $table->unsignedInteger('total_synced');
            $table->unsignedInteger('total_errors');
            $table->string('source_ip')->nullable();
            $table->timestamp('synced_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};

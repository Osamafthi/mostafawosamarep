<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 190)->unique();
            $table->string('password', 255);
            $table->timestamps();

            $table->index('email', 'idx_admins_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};

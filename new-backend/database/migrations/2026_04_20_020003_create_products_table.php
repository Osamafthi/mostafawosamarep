<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('slug', 220)->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->unsignedBigInteger('category_id');
            $table->string('image_url', 500)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('category_id', 'fk_products_category')
                ->references('id')
                ->on('categories')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('category_id', 'idx_products_category');
            $table->index('status', 'idx_products_status');
            $table->index('name', 'idx_products_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

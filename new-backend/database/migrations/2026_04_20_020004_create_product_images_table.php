<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('url', 500);
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('product_id', 'fk_product_images_product')
                ->references('id')
                ->on('products')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('product_id', 'idx_product_images_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};

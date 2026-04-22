<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name', 200);
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('order_id', 'fk_order_items_order')
                ->references('id')
                ->on('orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('product_id', 'fk_order_items_product')
                ->references('id')
                ->on('products')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index('order_id', 'idx_order_items_order');
            $table->index('product_id', 'idx_order_items_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

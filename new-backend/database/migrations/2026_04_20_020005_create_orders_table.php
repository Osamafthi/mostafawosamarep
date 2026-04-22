<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name', 200);
            $table->string('customer_email', 190);
            $table->string('customer_phone', 40)->nullable();
            $table->text('shipping_address');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->timestamps();

            $table->foreign('customer_id', 'fk_orders_customer')
                ->references('id')
                ->on('customers')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index('status', 'idx_orders_status');
            $table->index('payment_status', 'idx_orders_payment');
            $table->index('customer_email', 'idx_orders_email');
            $table->index('order_number', 'idx_orders_number');
            $table->index('customer_id', 'idx_orders_customer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

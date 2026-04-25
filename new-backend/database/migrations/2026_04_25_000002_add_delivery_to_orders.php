<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Assigned courier (auto-assigned at order creation, optionally
            // reassigned by admin). Nullable so we can fall back to "no
            // active courier" without blocking checkout.
            $table->unsignedBigInteger('delivery_person_id')->nullable()->after('customer_id');

            // Optional GPS share captured at checkout. Both columns must be
            // present together — enforced at the request-validation layer.
            $table->decimal('customer_latitude', 10, 7)->nullable()->after('shipping_address');
            $table->decimal('customer_longitude', 10, 7)->nullable()->after('customer_latitude');

            $table->foreign('delivery_person_id', 'fk_orders_delivery_person')
                ->references('id')
                ->on('delivery_persons')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index('delivery_person_id', 'idx_orders_delivery_person');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('fk_orders_delivery_person');
            $table->dropIndex('idx_orders_delivery_person');
            $table->dropColumn([
                'delivery_person_id',
                'customer_latitude',
                'customer_longitude',
            ]);
        });
    }
};

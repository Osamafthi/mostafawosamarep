<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Nullable until the customer clicks the verification link.
            $table->timestamp('email_verified_at')->nullable()->after('email');

            // Optional default shipping address captured on register so
            // checkout can be prefilled without an address book.
            $table->text('default_shipping_address')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['email_verified_at', 'default_shipping_address']);
        });
    }
};

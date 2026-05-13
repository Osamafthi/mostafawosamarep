<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration.
     *
     * Updates all products with zero stock to inactive status.
     * This is a one-time fix for existing data before automatic status management was implemented.
     */
    public function up(): void
    {
        $affected = DB::table('products')
            ->where('stock', '<=', 0)
            ->where('status', 'active')
            ->update(['status' => 'inactive']);

        echo "Updated {$affected} product(s) with zero stock to inactive status.\n";
    }

    /**
     * Reverse the migration.
     *
     * Note: This does not restore previous statuses as we don't track what they were.
     */
    public function down(): void
    {
        // This migration is not reversible as we don't know which products were intentionally inactive
        // vs those set inactive by this migration.
    }
};

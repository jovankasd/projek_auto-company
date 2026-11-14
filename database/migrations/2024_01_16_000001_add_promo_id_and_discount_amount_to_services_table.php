<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('promo_id')
                ->nullable()
                ->after('assigned_mechanic_id')
                ->constrained('promos')
                ->nullOnDelete();

            $table->decimal('discount_amount', 15, 2)
                ->default(0)
                ->after('estimated_cost');

            $table->index(['promo_id', 'status'], 'idx_services_promo_status');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['promo_id']);
            $table->dropIndex('idx_services_promo_status');
            $table->dropColumn(['promo_id', 'discount_amount']);
        });
    }
};

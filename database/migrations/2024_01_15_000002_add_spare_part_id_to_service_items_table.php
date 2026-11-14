<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_items', function (Blueprint $table) {
            $table->foreignId('spare_part_id')
                ->nullable()
                ->after('service_id')
                ->constrained('spare_parts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_items', function (Blueprint $table) {
            $table->dropForeign(['spare_part_id']);
            $table->dropColumn('spare_part_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_drives', function (Blueprint $table) {
            $table->string('ticket_number', 30)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('test_drives', function (Blueprint $table) {
            $table->dropUnique(['ticket_number']);
            $table->dropColumn('ticket_number');
        });
    }
};

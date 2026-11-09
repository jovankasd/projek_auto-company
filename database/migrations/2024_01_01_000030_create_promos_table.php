<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed', 'bonus']);
            $table->decimal('discount_value', 15, 2);
            $table->foreignId('car_id')->nullable()->constrained('cars')->nullOnDelete();
            $table->string('banner_image')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            $table->unsignedInteger('max_usage')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'starts_at', 'ends_at'], 'idx_promos_active_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};

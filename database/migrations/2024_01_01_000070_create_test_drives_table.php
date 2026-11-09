<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_drives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('car_id')->constrained('cars')->restrictOnDelete();
            $table->foreignId('sales_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('preferred_date');
            $table->string('driver_license_id', 50);
            $table->string('driver_license_image')->nullable();
            $table->string('pickup_location')->nullable();
            $table->enum('status', ['requested', 'approved', 'completed', 'rejected', 'cancelled'])->default('requested');
            $table->text('sales_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'preferred_date'], 'idx_td_status_date');
            $table->index(['sales_id', 'status'], 'idx_td_sales_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_drives');
    }
};

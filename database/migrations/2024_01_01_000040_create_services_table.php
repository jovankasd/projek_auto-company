<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 25)->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('vehicle_plate_number', 20);
            $table->string('vehicle_model', 100);
            $table->smallInteger('vehicle_year')->unsigned();
            $table->unsignedInteger('odometer_km');
            $table->enum('service_type', ['regular', 'major', 'inspection', 'warranty']);
            $table->dateTime('service_date');
            $table->tinyInteger('slot_hour')->unsigned();
            $table->text('complaint_notes');
            $table->foreignId('assigned_mechanic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('mechanic_notes')->nullable();
            $table->text('recommendation')->nullable();
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'service_date'], 'idx_services_status_date');
            $table->index(['assigned_mechanic_id', 'status'], 'idx_services_mechanic_status');
            $table->unique(['service_date', 'slot_hour', 'vehicle_plate_number'], 'uq_services_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

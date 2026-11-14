<?php

use App\Enums\ServiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create booking_slots table for mutex row-locking
        Schema::create('booking_slots', function (Blueprint $table) {
            $table->id();
            $table->string('slot_key', 30)->unique();
            $table->date('slot_date');
            $table->unsignedTinyInteger('slot_hour');
            $table->timestamps();

            $table->index(['slot_date', 'slot_hour'], 'idx_booking_slots_date_hour');
        });

        // 2. Add slot normalization columns to services table
        Schema::table('services', function (Blueprint $table) {
            $table->date('slot_date')->nullable()->after('service_date');
            $table->string('slot_key', 30)->nullable()->after('slot_date');
            $table->string('active_plate_slot', 60)->nullable()->after('slot_key');
        });

        // 3. Drop legacy composite unique index that included full datetime service_date
        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique('uq_services_slot');
        });

        // 4. Backfill existing service rows with normalized slot_date, slot_key, and active_plate_slot
        $seenSlots = [];
        DB::table('services')->orderBy('id')->chunkById(100, function ($services) use (&$seenSlots) {
            foreach ($services as $srv) {
                $parsedDate = Carbon::parse($srv->service_date);
                $date = $parsedDate->toDateString();
                $hour = (int) $srv->slot_hour;
                if ($hour < 8 || $hour > 17) {
                    $hour = 9;
                }
                $slotKey = sprintf('%s:%02d', $date, $hour);
                $cleanPlate = strtoupper(preg_replace('/\s+/', '', trim($srv->vehicle_plate_number)));

                $isActive = ! in_array($srv->status, ['cancelled', ServiceStatus::Cancelled->value], true)
                    && is_null($srv->deleted_at);

                $activePlateSlot = null;
                if ($isActive) {
                    $candidate = sprintf('%s:%s', $slotKey, $cleanPlate);
                    if (! isset($seenSlots[$candidate])) {
                        $activePlateSlot = $candidate;
                        $seenSlots[$candidate] = true;
                    }
                }

                DB::table('services')->where('id', $srv->id)->update([
                    'slot_date' => $date,
                    'slot_key' => $slotKey,
                    'active_plate_slot' => $activePlateSlot,
                ]);

                DB::table('booking_slots')->updateOrInsert(
                    ['slot_key' => $slotKey],
                    [
                        'slot_date' => $date,
                        'slot_hour' => $hour,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });

        // 5. Add unique index for active plate per slot and regular index on slot_key
        Schema::table('services', function (Blueprint $table) {
            $table->index('slot_key', 'idx_services_slot_key');
            $table->unique('active_plate_slot', 'uq_services_active_plate_slot');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique('uq_services_active_plate_slot');
            $table->dropIndex('idx_services_slot_key');
            $table->dropColumn(['active_plate_slot', 'slot_key', 'slot_date']);
            $table->unique(['service_date', 'slot_hour', 'vehicle_plate_number'], 'uq_services_slot');
        });

        Schema::dropIfExists('booking_slots');
    }
};

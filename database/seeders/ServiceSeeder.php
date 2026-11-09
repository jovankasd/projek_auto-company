<?php

namespace Database\Seeders;

use App\Enums\ServiceItemType;
use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Models\Service;
use App\Models\ServiceStatusLog;
use App\Models\User;
use App\Services\ServiceCostCalculator;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(ServiceCostCalculator $costCalculator): void
    {
        if (! app()->environment(['local', 'testing']) || in_array(config('app.env'), ['production', 'staging'], true)) {
            return;
        }

        $customer = User::where('role', UserRole::Customer)->first();
        $mekanik1 = User::where('email', 'mekanik1@xyzmotors.id')->first();
        $mekanik2 = User::where('email', 'mekanik2@xyzmotors.id')->first();

        if (! $customer || ! $mekanik1) {
            return;
        }

        $servicesData = [
            [
                'ticket_number' => 'SRV-20261001-0001',
                'customer_id' => $customer->id,
                'vehicle_plate_number' => 'B 1234 XYZ',
                'vehicle_model' => 'Ford Mustang GT',
                'vehicle_year' => 2024,
                'odometer_km' => 12500,
                'service_type' => 'regular',
                'service_date' => now()->startOfDay()->addHours(9),
                'slot_hour' => 9,
                'complaint_notes' => 'Servis berkala 10.000 KM dan ganti oli mesin.',
                'assigned_mechanic_id' => $mekanik1->id,
                'status' => ServiceStatus::InProgress,
                'confirmed_at' => now()->subHours(2),
                'started_at' => now()->subHour(),
                'items' => [
                    ['item_type' => ServiceItemType::Consumable, 'name' => 'Oli Mesin Fully Synthetic 5W-30 (5L)', 'quantity' => 1, 'unit_price' => 850000],
                    ['item_type' => ServiceItemType::Part, 'name' => 'Filter Oli OEM', 'quantity' => 1, 'unit_price' => 175000],
                    ['item_type' => ServiceItemType::Labor, 'name' => 'Jasa Servis Berkala', 'quantity' => 1, 'unit_price' => 350000],
                ],
                'logs' => [
                    ['from' => null, 'to' => 'pending', 'notes' => 'Booking servis dibuat oleh pelanggan.'],
                    ['from' => 'pending', 'to' => 'confirmed', 'notes' => 'Mekanik Agus ditugaskan.'],
                    ['from' => 'confirmed', 'to' => 'in_progress', 'notes' => 'Pengerjaan dimulai di stall 1.'],
                ],
            ],
            [
                'ticket_number' => 'SRV-20261001-0002',
                'customer_id' => $customer->id,
                'vehicle_plate_number' => 'B 5678 MTR',
                'vehicle_model' => 'BMW M4 Competition',
                'vehicle_year' => 2024,
                'odometer_km' => 8400,
                'service_type' => 'inspection',
                'service_date' => now()->startOfDay()->addHours(11),
                'slot_hour' => 11,
                'complaint_notes' => 'Pemeriksaan performa sistem pengereman dan scan komputer diagnostik.',
                'assigned_mechanic_id' => $mekanik2?->id ?? $mekanik1->id,
                'status' => ServiceStatus::Confirmed,
                'confirmed_at' => now()->subHours(4),
                'items' => [
                    ['item_type' => ServiceItemType::Labor, 'name' => 'General Inspection & Brake Check', 'quantity' => 1, 'unit_price' => 500000],
                ],
                'logs' => [
                    ['from' => null, 'to' => 'pending', 'notes' => 'Booking inspeksi dibuat.'],
                    ['from' => 'pending', 'to' => 'confirmed', 'notes' => 'Jadwal dikonfirmasi, menunggu unit tiba.'],
                ],
            ],
            [
                'ticket_number' => 'SRV-20260928-0003',
                'customer_id' => $customer->id,
                'vehicle_plate_number' => 'B 9999 LUX',
                'vehicle_model' => 'Toyota Alphard',
                'vehicle_year' => 2023,
                'odometer_km' => 25000,
                'service_type' => 'major',
                'service_date' => now()->subDays(3)->addHours(10),
                'slot_hour' => 10,
                'complaint_notes' => 'Servis besar 20.000 KM, tune-up, kuras minyak rem.',
                'assigned_mechanic_id' => $mekanik1->id,
                'status' => ServiceStatus::Completed,
                'confirmed_at' => now()->subDays(3)->addHours(8),
                'started_at' => now()->subDays(3)->addHours(10),
                'completed_at' => now()->subDays(3)->addHours(15),
                'items' => [
                    ['item_type' => ServiceItemType::Consumable, 'name' => 'Oli Mesin 0W-20 (4L)', 'quantity' => 1, 'unit_price' => 600000],
                    ['item_type' => ServiceItemType::Part, 'name' => 'Filter Udara & Filter AC', 'quantity' => 1, 'unit_price' => 380000],
                    ['item_type' => ServiceItemType::Consumable, 'name' => 'Minyak Rem DOT 4', 'quantity' => 2, 'unit_price' => 120000],
                    ['item_type' => ServiceItemType::Labor, 'name' => 'Jasa Paket Major Service', 'quantity' => 1, 'unit_price' => 950000],
                ],
                'logs' => [
                    ['from' => null, 'to' => 'pending', 'notes' => 'Booking dibuat.'],
                    ['from' => 'pending', 'to' => 'confirmed', 'notes' => 'Dikonfirmasi.'],
                    ['from' => 'confirmed', 'to' => 'in_progress', 'notes' => 'Pengerjaan dimulai.'],
                    ['from' => 'in_progress', 'to' => 'completed', 'notes' => 'Servis selesai dan siap diambil.'],
                ],
            ],
            [
                'ticket_number' => 'SRV-20261001-0004',
                'customer_id' => $customer->id,
                'vehicle_plate_number' => 'D 1111 EV',
                'vehicle_model' => 'Tesla Model 3',
                'vehicle_year' => 2024,
                'odometer_km' => 5000,
                'service_type' => 'inspection',
                'service_date' => now()->startOfDay()->addHours(14),
                'slot_hour' => 14,
                'complaint_notes' => 'Pengecekan baterai pendingin dan rotasi ban.',
                'assigned_mechanic_id' => null,
                'status' => ServiceStatus::Pending,
                'items' => [],
                'logs' => [
                    ['from' => null, 'to' => 'pending', 'notes' => 'Booking baru masuk via showroom.'],
                ],
            ],
            [
                'ticket_number' => 'SRV-20260920-0005',
                'customer_id' => $customer->id,
                'vehicle_plate_number' => 'B 4444 RPT',
                'vehicle_model' => 'Ford Ranger Raptor',
                'vehicle_year' => 2024,
                'odometer_km' => 15000,
                'service_type' => 'regular',
                'service_date' => now()->subDays(10)->addHours(13),
                'slot_hour' => 13,
                'complaint_notes' => 'Pelanggan membatalkan janji karena perjalanan dinas luar kota.',
                'assigned_mechanic_id' => null,
                'status' => ServiceStatus::Cancelled,
                'cancelled_at' => now()->subDays(10)->addHours(11),
                'cancellation_reason' => 'Perjalanan dinas ke luar kota mendadak.',
                'items' => [],
                'logs' => [
                    ['from' => null, 'to' => 'pending', 'notes' => 'Booking servis dibuat.'],
                    ['from' => 'pending', 'to' => 'cancelled', 'notes' => 'Dibatalkan oleh pelanggan.'],
                ],
            ],
            [
                'ticket_number' => 'SRV-20261001-0006',
                'customer_id' => $customer->id,
                'vehicle_plate_number' => 'B 7777 WNT',
                'vehicle_model' => 'Ford Mustang GT',
                'vehicle_year' => 2024,
                'odometer_km' => 3000,
                'service_type' => 'warranty',
                'service_date' => now()->startOfDay()->addHours(15),
                'slot_hour' => 15,
                'complaint_notes' => 'Klaim garansi bunyi decit pada door trim kanan.',
                'assigned_mechanic_id' => $mekanik1->id,
                'status' => ServiceStatus::Confirmed,
                'confirmed_at' => now()->subHours(1),
                'items' => [
                    ['item_type' => ServiceItemType::Labor, 'name' => 'Pemeriksaan Garansi & Re-clip Door Trim', 'quantity' => 1, 'unit_price' => 0],
                ],
                'logs' => [
                    ['from' => null, 'to' => 'pending', 'notes' => 'Klaim garansi diajukan.'],
                    ['from' => 'pending', 'to' => 'confirmed', 'notes' => 'Diterima untuk inspeksi.'],
                ],
            ],
            [
                'ticket_number' => 'SRV-20260925-0007',
                'customer_id' => $customer->id,
                'vehicle_plate_number' => 'B 2222 MTR',
                'vehicle_model' => 'BMW M4 Competition',
                'vehicle_year' => 2024,
                'odometer_km' => 18000,
                'service_type' => 'regular',
                'service_date' => now()->subDays(6)->addHours(10),
                'slot_hour' => 10,
                'complaint_notes' => 'Penggantian kanvas rem depan dan belakang.',
                'assigned_mechanic_id' => $mekanik2?->id ?? $mekanik1->id,
                'status' => ServiceStatus::Completed,
                'started_at' => now()->subDays(6)->addHours(10),
                'completed_at' => now()->subDays(6)->addHours(13),
                'items' => [
                    ['item_type' => ServiceItemType::Part, 'name' => 'Brake Pad Front Set M-Sport', 'quantity' => 1, 'unit_price' => 3200000],
                    ['item_type' => ServiceItemType::Part, 'name' => 'Brake Pad Rear Set M-Sport', 'quantity' => 1, 'unit_price' => 2600000],
                    ['item_type' => ServiceItemType::Labor, 'name' => 'Jasa Ganti Kanvas Rem + Bleeding', 'quantity' => 1, 'unit_price' => 600000],
                ],
                'logs' => [
                    ['from' => null, 'to' => 'pending', 'notes' => 'Booking dibuat.'],
                    ['from' => 'pending', 'to' => 'confirmed', 'notes' => 'Dikonfirmasi.'],
                    ['from' => 'confirmed', 'to' => 'in_progress', 'notes' => 'Mulai pengerjaan.'],
                    ['from' => 'in_progress', 'to' => 'completed', 'notes' => 'Pengerjaan selesai.'],
                ],
            ],
            [
                'ticket_number' => 'SRV-20261001-0008',
                'customer_id' => $customer->id,
                'vehicle_plate_number' => 'B 8888 RAP',
                'vehicle_model' => 'Ford Ranger Raptor',
                'vehicle_year' => 2024,
                'odometer_km' => 21000,
                'service_type' => 'regular',
                'service_date' => now()->startOfDay()->addHours(16),
                'slot_hour' => 16,
                'complaint_notes' => 'Pengecekan suspensi Fox Racing dan spooring balancing.',
                'assigned_mechanic_id' => $mekanik1->id,
                'status' => ServiceStatus::InProgress,
                'started_at' => now()->subMinutes(30),
                'items' => [
                    ['item_type' => ServiceItemType::Labor, 'name' => 'Spooring 3D & Balancing 4 Roda', 'quantity' => 1, 'unit_price' => 450000],
                    ['item_type' => ServiceItemType::Labor, 'name' => 'Suspension Diagnostic & Torque Check', 'quantity' => 1, 'unit_price' => 300000],
                ],
                'logs' => [
                    ['from' => null, 'to' => 'pending', 'notes' => 'Booking servis dibuat.'],
                    ['from' => 'pending', 'to' => 'confirmed', 'notes' => 'Dikonfirmasi oleh mekanik.'],
                    ['from' => 'confirmed', 'to' => 'in_progress', 'notes' => 'Sedang uji spooring dan suspensi.'],
                ],
            ],
        ];

        foreach ($servicesData as $data) {
            $items = $data['items'];
            $logs = $data['logs'];
            unset($data['items'], $data['logs']);

            $service = Service::firstOrCreate(
                ['ticket_number' => $data['ticket_number']],
                $data
            );

            if ($service->wasRecentlyCreated) {
                foreach ($items as $item) {
                    $service->items()->create([
                        'item_type' => $item['item_type'],
                        'name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['quantity'] * $item['unit_price'],
                        'added_by' => $service->assigned_mechanic_id ?? $mekanik1->id,
                    ]);
                }

                $costCalculator->recalculate($service);

                foreach ($logs as $log) {
                    ServiceStatusLog::create([
                        'service_id' => $service->id,
                        'from_status' => $log['from'],
                        'to_status' => $log['to'],
                        'changed_by' => $service->assigned_mechanic_id ?? $mekanik1->id,
                        'notes' => $log['notes'],
                    ]);
                }
            }
        }
    }
}

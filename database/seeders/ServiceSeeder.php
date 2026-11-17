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
        $mekanik1 = User::where('email', 'mekanik1@xyzmotors.id')->first()
            ?? User::where('role', UserRole::Mekanik)->where('is_active', true)->first();
        $mekanik2 = User::where('email', 'mekanik2@xyzmotors.id')->first()
            ?? User::where('role', UserRole::Mekanik)->where('is_active', true)->skip(1)->first()
            ?? $mekanik1;

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

        $servicesData = array_merge(
            $servicesData,
            $this->buildChartDummyServices($customer->id, $mekanik1->id, $mekanik2?->id ?? $mekanik1->id)
        );

        Service::withoutEvents(function () use ($servicesData, $costCalculator, $mekanik1) {
            foreach ($servicesData as $data) {
                $items = $data['items'];
                $logs = $data['logs'];
                $createdAtOverride = $data['created_at_override'] ?? null;
                unset($data['items'], $data['logs'], $data['created_at_override']);

                $service = Service::firstOrCreate(
                    ['ticket_number' => $data['ticket_number']],
                    $data
                );

                if ($service->wasRecentlyCreated) {
                    if ($createdAtOverride) {
                        $service->forceFill([
                            'created_at' => $createdAtOverride,
                            'updated_at' => $data['completed_at'] ?? $createdAtOverride,
                        ])->saveQuietly();
                    }

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
        });
    }

    /**
     * Hasilkan data servis dummy lintas hari ini, 30 hari terakhir, dan 5 bulan sebelumnya
     * agar seluruh grafik di panel Owner, Super Admin, dan Admin Mekanik terlihat jelas.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildChartDummyServices(int $customerId, int $mekanik1Id, int $mekanik2Id): array
    {
        $vehicles = [
            ['model' => 'Ford Mustang GT', 'year' => 2024, 'part' => 'Brake Pad Set', 'part_price' => 2500000],
            ['model' => 'BMW M4 Competition', 'year' => 2024, 'part' => 'Air Filter', 'part_price' => 850000],
            ['model' => 'Toyota Alphard', 'year' => 2024, 'part' => 'Oil Filter', 'part_price' => 150000],
            ['model' => 'Tesla Model 3', 'year' => 2024, 'part' => 'Cabin Air Filter', 'part_price' => 600000],
            ['model' => 'Ford Ranger Raptor', 'year' => 2024, 'part' => 'Shock Absorber Front', 'part_price' => 5500000],
        ];

        $rows = [];

        // 1. Data hari ini (5 unit: 2 selesai hari ini, 1 in_progress, 1 confirmed, 1 pending unassigned)
        $todaySpecs = [
            ['hour' => 8, 'status' => ServiceStatus::Completed, 'mech' => $mekanik1Id, 'type' => 'regular', 'labor' => 450000, 'oil' => 750000],
            ['hour' => 9, 'status' => ServiceStatus::Completed, 'mech' => $mekanik2Id, 'type' => 'major', 'labor' => 900000, 'oil' => 850000],
            ['hour' => 10, 'status' => ServiceStatus::InProgress, 'mech' => $mekanik1Id, 'type' => 'regular', 'labor' => 350000, 'oil' => 650000],
            ['hour' => 13, 'status' => ServiceStatus::Confirmed, 'mech' => $mekanik2Id, 'type' => 'inspection', 'labor' => 400000, 'oil' => 0],
            ['hour' => 15, 'status' => ServiceStatus::Pending, 'mech' => null, 'type' => 'warranty', 'labor' => 0, 'oil' => 0],
        ];

        foreach ($todaySpecs as $i => $spec) {
            $v = $vehicles[$i % count($vehicles)];
            $date = now()->startOfDay()->addHours($spec['hour']);
            $isDone = $spec['status'] === ServiceStatus::Completed;
            $items = [];
            if ($spec['labor'] > 0) {
                $items[] = ['item_type' => ServiceItemType::Labor, 'name' => 'Jasa Pengerjaan & Inspeksi Bengkel', 'quantity' => 1, 'unit_price' => $spec['labor']];
                $items[] = ['item_type' => ServiceItemType::Part, 'name' => $v['part'], 'quantity' => 1, 'unit_price' => $v['part_price']];
            }
            if ($spec['oil'] > 0) {
                $items[] = ['item_type' => ServiceItemType::Consumable, 'name' => 'Oli Mesin Synthetic & Pelumas', 'quantity' => 1, 'unit_price' => $spec['oil']];
            }

            $rows[] = [
                'ticket_number' => 'SRV-'.$date->format('Ymd').'-'.str_pad((string) (101 + $i), 4, '0', STR_PAD_LEFT),
                'customer_id' => $customerId,
                'vehicle_plate_number' => 'B '.(3101 + $i).' AC',
                'vehicle_model' => $v['model'],
                'vehicle_year' => $v['year'],
                'odometer_km' => 10000 + ($i * 2500),
                'service_type' => $spec['type'],
                'service_date' => $date,
                'slot_hour' => $spec['hour'],
                'complaint_notes' => 'Pekerjaan servis harian AUTO COMPANY.',
                'assigned_mechanic_id' => $spec['mech'],
                'status' => $spec['status'],
                'confirmed_at' => $spec['status'] !== ServiceStatus::Pending ? now()->subHours(4) : null,
                'started_at' => in_array($spec['status'], [ServiceStatus::InProgress, ServiceStatus::Completed], true) ? now()->subHours(3) : null,
                'completed_at' => $isDone ? now()->subHours(1 + $i) : null,
                'created_at_override' => now()->subHours(5),
                'items' => $items,
                'logs' => [
                    ['from' => null, 'to' => 'pending', 'notes' => 'Booking servis dibuat.'],
                    ['from' => 'pending', 'to' => $spec['status']->value, 'notes' => 'Status awal data operasional hari ini.'],
                ],
            ];
        }

        // 2. Data 30 hari terakhir (sebar 22 tiket pada 1-28 hari terakhir agar grafik 30 hari & 7 hari terisi merata)
        $thirtyDayOffsets = [
            [1, 9, ServiceStatus::Completed], [1, 14, ServiceStatus::Completed],
            [2, 10, ServiceStatus::Completed], [2, 15, ServiceStatus::InProgress],
            [3, 11, ServiceStatus::Completed],
            [4, 9, ServiceStatus::Completed], [4, 13, ServiceStatus::Confirmed],
            [5, 10, ServiceStatus::Completed], [5, 16, ServiceStatus::Completed],
            [6, 11, ServiceStatus::Completed],
            [8, 9, ServiceStatus::Completed], [9, 14, ServiceStatus::Completed],
            [11, 10, ServiceStatus::Completed], [12, 9, ServiceStatus::Completed],
            [12, 15, ServiceStatus::Completed], [14, 11, ServiceStatus::Completed],
            [16, 10, ServiceStatus::Completed], [18, 13, ServiceStatus::Completed],
            [20, 9, ServiceStatus::Completed], [22, 14, ServiceStatus::Completed],
            [25, 10, ServiceStatus::Completed], [27, 11, ServiceStatus::Completed],
        ];

        $types = ['regular', 'major', 'inspection', 'warranty'];
        foreach ($thirtyDayOffsets as $i => [$daysAgo, $hour, $status]) {
            $v = $vehicles[$i % count($vehicles)];
            $date = now()->subDays($daysAgo)->startOfDay()->addHours($hour);
            $mechId = $i % 2 === 0 ? $mekanik1Id : $mekanik2Id;

            $rows[] = [
                'ticket_number' => 'SRV-'.$date->format('Ymd').'-'.str_pad((string) (201 + $i), 4, '0', STR_PAD_LEFT),
                'customer_id' => $customerId,
                'vehicle_plate_number' => 'B '.(4101 + $i).' AC',
                'vehicle_model' => $v['model'],
                'vehicle_year' => $v['year'],
                'odometer_km' => 14000 + ($i * 1200),
                'service_type' => $types[$i % count($types)],
                'service_date' => $date,
                'slot_hour' => $hour,
                'complaint_notes' => 'Perawatan berkala dan inspeksi sistem kendaraan.',
                'assigned_mechanic_id' => $mechId,
                'status' => $status,
                'confirmed_at' => $date->copy()->subHours(2),
                'started_at' => $date->copy(),
                'completed_at' => $status === ServiceStatus::Completed ? $date->copy()->addHours(3) : null,
                'created_at_override' => $date->copy()->subDay(),
                'items' => [
                    ['item_type' => ServiceItemType::Part, 'name' => $v['part'], 'quantity' => 1, 'unit_price' => $v['part_price']],
                    ['item_type' => ServiceItemType::Consumable, 'name' => 'Oli Mesin Fully Synthetic 5W-30', 'quantity' => 1, 'unit_price' => 750000],
                    ['item_type' => ServiceItemType::Labor, 'name' => 'Jasa Servis & Tune Up', 'quantity' => 1, 'unit_price' => 450000 + (($i % 3) * 150000)],
                ],
                'logs' => [
                    ['from' => null, 'to' => 'pending', 'notes' => 'Booking dibuat oleh pelanggan.'],
                    ['from' => 'pending', 'to' => $status->value, 'notes' => 'Diproses mekanik AUTO COMPANY.'],
                ],
            ];
        }

        // 3. Data historis 5 bulan sebelumnya (3 transaksi selesai per bulan agar grafik 6 bulan Owner lengkap)
        $monthlyPresets = [
            5 => [1200000, 1850000, 2500000],
            4 => [2500000, 3200000, 1600000],
            3 => [5500000, 2500000, 1850000],
            2 => [2500000, 4800000, 1850000],
            1 => [5500000, 2500000, 3200000],
        ];

        $seq = 301;
        foreach ($monthlyPresets as $monthsAgo => $partPrices) {
            foreach ($partPrices as $idx => $partPrice) {
                $v = $vehicles[($monthsAgo + $idx) % count($vehicles)];
                $date = now()->subMonths($monthsAgo)->startOfMonth()->addDays(4 + ($idx * 7))->setTime(10 + $idx, 0);
                $mechId = $idx % 2 === 0 ? $mekanik1Id : $mekanik2Id;

                $rows[] = [
                    'ticket_number' => 'SRV-'.$date->format('Ymd').'-'.str_pad((string) $seq++, 4, '0', STR_PAD_LEFT),
                    'customer_id' => $customerId,
                    'vehicle_plate_number' => 'B '.(5000 + $seq).' AC',
                    'vehicle_model' => $v['model'],
                    'vehicle_year' => $v['year'],
                    'odometer_km' => 18000 + ($seq * 100),
                    'service_type' => $idx === 1 ? 'major' : 'regular',
                    'service_date' => $date,
                    'slot_hour' => 10 + $idx,
                    'complaint_notes' => 'Servis berkala & penggantian komponen berkala.',
                    'assigned_mechanic_id' => $mechId,
                    'status' => ServiceStatus::Completed,
                    'confirmed_at' => $date->copy()->subHours(2),
                    'started_at' => $date->copy(),
                    'completed_at' => $date->copy()->addHours(4),
                    'created_at_override' => $date->copy()->subDay(),
                    'items' => [
                        ['item_type' => ServiceItemType::Part, 'name' => $v['part'], 'quantity' => 1, 'unit_price' => $partPrice],
                        ['item_type' => ServiceItemType::Consumable, 'name' => 'Paket Pelumas & Coolant', 'quantity' => 1, 'unit_price' => 650000],
                        ['item_type' => ServiceItemType::Labor, 'name' => 'Jasa Pengerjaan Mekanik', 'quantity' => 1, 'unit_price' => 550000],
                    ],
                    'logs' => [
                        ['from' => null, 'to' => 'pending', 'notes' => 'Booking dibuat.'],
                        ['from' => 'pending', 'to' => 'completed', 'notes' => 'Servis diselesaikan dan lunas.'],
                    ],
                ];
            }
        }

        return $rows;
    }
}

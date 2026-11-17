<?php

namespace Database\Seeders;

use App\Enums\TestDriveStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\TestDrive;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDriveSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) || in_array(config('app.env'), ['production', 'staging'], true)) {
            return;
        }

        $customer = User::where('role', UserRole::Customer)->first();
        $sales = User::where('role', UserRole::Sales)->where('is_active', true)->first()
            ?? User::where('role', UserRole::Sales)->first();
        $fallbackCar = Car::first();

        if (! $customer || ! $sales || ! $fallbackCar) {
            return;
        }

        $mustang = Car::where('slug', 'ford-mustang-gt-2024')->first() ?? $fallbackCar;
        $bmw = Car::where('slug', 'bmw-m4-competition-2024')->first() ?? $fallbackCar;
        $alphard = Car::where('slug', 'toyota-alphard-2024')->first() ?? $fallbackCar;
        $tesla = Car::where('slug', 'tesla-model-3-2024')->first() ?? $fallbackCar;
        $raptor = Car::where('slug', 'ford-ranger-raptor-2024')->first() ?? $fallbackCar;

        $testDrivesData = [
            [
                'ticket_number' => 'TD-20261115-0001',
                'customer_id' => $customer->id,
                'car_id' => $mustang->id,
                'sales_id' => $sales->id,
                'preferred_date' => now()->addDays(1)->setTime(10, 0),
                'driver_license_id' => '3171012345678901',
                'pickup_location' => 'AUTO COMPANY Showroom Pusat',
                'status' => TestDriveStatus::Requested,
                'sales_notes' => 'Customer tertarik dengan varian Race Red.',
            ],
            [
                'ticket_number' => 'TD-20261115-0002',
                'customer_id' => $customer->id,
                'car_id' => $bmw->id,
                'sales_id' => $sales->id,
                'preferred_date' => now()->addDays(2)->setTime(14, 0),
                'driver_license_id' => '3171012345678902',
                'pickup_location' => 'Kantor Customer (Kuningan, Jaksel)',
                'status' => TestDriveStatus::Approved,
                'approved_at' => now()->subHour(),
                'sales_notes' => 'Unit BMW M4 telah disiapkan dengan bahan bakar penuh.',
            ],
            [
                'ticket_number' => 'TD-20261115-0003',
                'customer_id' => $customer->id,
                'car_id' => $tesla->id,
                'sales_id' => $sales->id,
                'preferred_date' => now()->subDays(3)->setTime(11, 0),
                'driver_license_id' => '3171012345678903',
                'pickup_location' => 'AUTO COMPANY Showroom Pusat',
                'status' => TestDriveStatus::Completed,
                'approved_at' => now()->subDays(4),
                'completed_at' => now()->subDays(3)->setTime(12, 0),
                'sales_notes' => 'Test drive sukses 45 menit, customer sedang mempertimbangkan opsi kredit.',
            ],
            [
                'ticket_number' => 'TD-20261115-0004',
                'customer_id' => $customer->id,
                'car_id' => $raptor->id,
                'sales_id' => $sales->id,
                'preferred_date' => now()->subDays(1)->setTime(15, 0),
                'driver_license_id' => '3171012345678904',
                'pickup_location' => 'AUTO COMPANY Showroom Pusat',
                'status' => TestDriveStatus::Rejected,
                'rejection_reason' => 'Unit Ranger Raptor sedang dalam jadwal inspeksi berkala pada jam tersebut.',
                'sales_notes' => 'Disarankan untuk reschedule ke hari berikutnya.',
            ],
            [
                'ticket_number' => 'TD-20261115-0005',
                'customer_id' => $customer->id,
                'car_id' => $alphard->id,
                'sales_id' => $sales->id,
                'preferred_date' => now()->addDays(5)->setTime(16, 0),
                'driver_license_id' => '3171012345678905',
                'pickup_location' => 'Rumah Customer (Pondok Indah)',
                'status' => TestDriveStatus::Requested,
                'sales_notes' => 'Customer menanyakan ketersediaan captain seat warna beige.',
            ],
        ];

        $historicalLeads = [
            ['months_ago' => 5, 'day' => 10, 'hour' => 10, 'car' => $mustang, 'status' => TestDriveStatus::Completed, 'location' => 'AUTO COMPANY Showroom Pusat'],
            ['months_ago' => 5, 'day' => 22, 'hour' => 14, 'car' => $alphard, 'status' => TestDriveStatus::Completed, 'location' => 'Rumah Customer (Menteng)'],
            ['months_ago' => 4, 'day' => 8, 'hour' => 11, 'car' => $bmw, 'status' => TestDriveStatus::Completed, 'location' => 'AUTO COMPANY Showroom Pusat'],
            ['months_ago' => 4, 'day' => 16, 'hour' => 15, 'car' => $tesla, 'status' => TestDriveStatus::Completed, 'location' => 'SCBD Jakarta Selatan'],
            ['months_ago' => 4, 'day' => 25, 'hour' => 13, 'car' => $raptor, 'status' => TestDriveStatus::Rejected, 'location' => 'AUTO COMPANY Showroom Pusat'],
            ['months_ago' => 3, 'day' => 6, 'hour' => 10, 'car' => $mustang, 'status' => TestDriveStatus::Completed, 'location' => 'AUTO COMPANY Showroom Pusat'],
            ['months_ago' => 3, 'day' => 14, 'hour' => 16, 'car' => $alphard, 'status' => TestDriveStatus::Completed, 'location' => 'Kemang Raya, Jaksel'],
            ['months_ago' => 3, 'day' => 21, 'hour' => 11, 'car' => $tesla, 'status' => TestDriveStatus::Approved, 'location' => 'AUTO COMPANY Showroom Pusat'],
            ['months_ago' => 2, 'day' => 7, 'hour' => 14, 'car' => $bmw, 'status' => TestDriveStatus::Completed, 'location' => 'AUTO COMPANY Showroom Pusat'],
            ['months_ago' => 2, 'day' => 18, 'hour' => 10, 'car' => $raptor, 'status' => TestDriveStatus::Completed, 'location' => 'Pantai Indah Kapuk'],
            ['months_ago' => 2, 'day' => 24, 'hour' => 15, 'car' => $mustang, 'status' => TestDriveStatus::Rejected, 'location' => 'AUTO COMPANY Showroom Pusat'],
            ['months_ago' => 1, 'day' => 5, 'hour' => 10, 'car' => $tesla, 'status' => TestDriveStatus::Completed, 'location' => 'AUTO COMPANY Showroom Pusat'],
            ['months_ago' => 1, 'day' => 12, 'hour' => 13, 'car' => $alphard, 'status' => TestDriveStatus::Completed, 'location' => 'Senopati, Jaksel'],
            ['months_ago' => 1, 'day' => 19, 'hour' => 15, 'car' => $bmw, 'status' => TestDriveStatus::Completed, 'location' => 'AUTO COMPANY Showroom Pusat'],
            ['months_ago' => 1, 'day' => 26, 'hour' => 11, 'car' => $mustang, 'status' => TestDriveStatus::Approved, 'location' => 'AUTO COMPANY Showroom Pusat'],
            ['months_ago' => 0, 'day' => -2, 'hour' => 10, 'car' => $mustang, 'status' => TestDriveStatus::Requested, 'location' => 'AUTO COMPANY Showroom Pusat'],
            ['months_ago' => 0, 'day' => -1, 'hour' => 13, 'car' => $bmw, 'status' => TestDriveStatus::Requested, 'location' => 'Kantor Customer (Sudirman)'],
            ['months_ago' => 0, 'day' => 0, 'hour' => 11, 'car' => $raptor, 'status' => TestDriveStatus::Requested, 'location' => 'AUTO COMPANY Showroom Pusat'],
            ['months_ago' => 0, 'day' => 1, 'hour' => 14, 'car' => $tesla, 'status' => TestDriveStatus::Approved, 'location' => 'AUTO COMPANY Showroom Pusat'],
            ['months_ago' => 0, 'day' => 2, 'hour' => 16, 'car' => $alphard, 'status' => TestDriveStatus::Requested, 'location' => 'Pondok Indah, Jaksel'],
        ];

        foreach ($historicalLeads as $idx => $hl) {
            $prefDate = $hl['months_ago'] > 0
                ? now()->subMonths($hl['months_ago'])->startOfMonth()->addDays($hl['day'] - 1)->setTime($hl['hour'], 0)
                : now()->addDays($hl['day'])->setTime($hl['hour'], 0);

            $licenseNum = (string) (3171012345678906 + $idx);
            $ticketNum = 'TD-'.$prefDate->format('Ymd').'-'.str_pad((string) (101 + $idx), 4, '0', STR_PAD_LEFT);

            $testDrivesData[] = [
                'ticket_number' => $ticketNum,
                'customer_id' => $customer->id,
                'car_id' => $hl['car']->id,
                'sales_id' => $hl['status'] === TestDriveStatus::Requested && $idx % 2 === 0 ? null : $sales->id,
                'preferred_date' => $prefDate,
                'driver_license_id' => $licenseNum,
                'pickup_location' => $hl['location'],
                'status' => $hl['status'],
                'approved_at' => in_array($hl['status'], [TestDriveStatus::Approved, TestDriveStatus::Completed], true)
                    ? $prefDate->copy()->subHours(6)
                    : null,
                'completed_at' => $hl['status'] === TestDriveStatus::Completed
                    ? $prefDate->copy()->addHour()
                    : null,
                'rejection_reason' => $hl['status'] === TestDriveStatus::Rejected
                    ? 'Jadwal unit bentrok dengan agenda maintenance showroom.'
                    : null,
                'sales_notes' => 'Lead dummy untuk analitik showroom AUTO COMPANY.',
                'created_at_override' => $prefDate->copy()->subDay(),
            ];
        }

        TestDrive::withoutEvents(function () use ($testDrivesData) {
            foreach ($testDrivesData as $data) {
                $createdAtOverride = $data['created_at_override'] ?? null;
                unset($data['created_at_override']);

                $td = TestDrive::firstOrCreate(
                    ['driver_license_id' => $data['driver_license_id']],
                    $data
                );

                if ($td->wasRecentlyCreated && $createdAtOverride) {
                    $td->forceFill([
                        'created_at' => $createdAtOverride,
                        'updated_at' => $data['completed_at'] ?? $data['approved_at'] ?? $createdAtOverride,
                    ])->saveQuietly();
                }
            }
        });
    }
}

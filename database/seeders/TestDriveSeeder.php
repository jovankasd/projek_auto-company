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
        $customer = User::where('role', UserRole::Customer)->first();
        $sales = User::where('role', UserRole::Sales)->first();

        if (! $customer || ! $sales) {
            return;
        }

        $mustang = Car::where('slug', 'ford-mustang-gt-2024')->first();
        $bmw = Car::where('slug', 'bmw-m4-competition-2024')->first();
        $alphard = Car::where('slug', 'toyota-alphard-2024')->first() ?? Car::first();
        $tesla = Car::where('slug', 'tesla-model-3-highland-2024')->first() ?? Car::first();
        $raptor = Car::where('slug', 'ford-ranger-raptor-2024')->first() ?? Car::first();

        $testDrivesData = [
            [
                'customer_id' => $customer->id,
                'car_id' => $mustang?->id,
                'sales_id' => $sales->id,
                'preferred_date' => now()->addDays(1)->setHour(10)->setMinute(0),
                'driver_license_id' => '3171012345678901',
                'pickup_location' => 'XYZ Motors Showroom Pusat',
                'status' => TestDriveStatus::Requested,
                'sales_notes' => 'Customer tertarik dengan varian Race Red.',
            ],
            [
                'customer_id' => $customer->id,
                'car_id' => $bmw?->id,
                'sales_id' => $sales->id,
                'preferred_date' => now()->addDays(2)->setHour(14)->setMinute(0),
                'driver_license_id' => '3171012345678902',
                'pickup_location' => 'Kantor Customer (Kuningan, Jaksel)',
                'status' => TestDriveStatus::Approved,
                'approved_at' => now()->subHour(),
                'sales_notes' => 'Unit BMW M4 telah disiapkan dengan bahan bakar penuh.',
            ],
            [
                'customer_id' => $customer->id,
                'car_id' => $tesla?->id,
                'sales_id' => $sales->id,
                'preferred_date' => now()->subDays(3)->setHour(11)->setMinute(0),
                'driver_license_id' => '3171012345678903',
                'pickup_location' => 'XYZ Motors Showroom Pusat',
                'status' => TestDriveStatus::Completed,
                'approved_at' => now()->subDays(4),
                'completed_at' => now()->subDays(3)->setHour(12)->setMinute(0),
                'sales_notes' => 'Test drive sukses 45 menit, customer sedang mempertimbangkan opsi kredit.',
            ],
            [
                'customer_id' => $customer->id,
                'car_id' => $raptor?->id,
                'sales_id' => $sales->id,
                'preferred_date' => now()->subDays(1)->setHour(15)->setMinute(0),
                'driver_license_id' => '3171012345678904',
                'pickup_location' => 'XYZ Motors Showroom Pusat',
                'status' => TestDriveStatus::Rejected,
                'rejection_reason' => 'Unit Ranger Raptor sedang dalam jadwal inspeksi berkala pada jam tersebut.',
                'sales_notes' => 'Disarankan untuk reschedule ke hari berikutnya.',
            ],
            [
                'customer_id' => $customer->id,
                'car_id' => $alphard?->id,
                'sales_id' => $sales->id,
                'preferred_date' => now()->addDays(5)->setHour(16)->setMinute(0),
                'driver_license_id' => '3171012345678905',
                'pickup_location' => 'Rumah Customer (Pondok Indah)',
                'status' => TestDriveStatus::Requested,
                'sales_notes' => 'Customer menanyakan ketersediaan captain seat warna beige.',
            ],
        ];

        foreach ($testDrivesData as $data) {
            TestDrive::firstOrCreate(
                [
                    'customer_id' => $data['customer_id'],
                    'car_id' => $data['car_id'],
                    'preferred_date' => $data['preferred_date'],
                ],
                $data
            );
        }
    }
}

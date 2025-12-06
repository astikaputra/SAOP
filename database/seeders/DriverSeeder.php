<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Driver;
use App\Models\User;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        // Get driver users
        $driver1 = User::where('email', 'joko@pelabuhan.com')->first();
        $driver2 = User::where('email', 'rina@pelabuhan.com')->first();

        if ($driver1) {
            Driver::create([
                'user_id' => $driver1->id,
                'driver_id' => 'DRV-001',
                'vehicle_type' => 'PICKUP_TRANSPORTASI',
                'vehicle_number' => 'B 1234 ABC',
                'vehicle_brand' => 'Toyota',
                'vehicle_model' => 'Hilux',
                'vehicle_year' => 2022,
                'vehicle_color' => 'Hitam',
                'status' => 'AVAILABLE',
                'total_trips' => 45,
                'total_earnings' => 4500000,
                'rating' => 4.7,
                'rating_count' => 45,
                'shift_start' => '08:00',
                'shift_end' => '17:00',
                'working_days' => json_encode(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'])
            ]);
        }

        if ($driver2) {
            Driver::create([
                'user_id' => $driver2->id,
                'driver_id' => 'DRV-002',
                'vehicle_type' => 'MOTOR_150CC',
                'vehicle_number' => 'B 5678 XYZ',
                'vehicle_brand' => 'Honda',
                'vehicle_model' => 'Vario',
                'vehicle_year' => 2023,
                'vehicle_color' => 'Merah',
                'status' => 'AVAILABLE',
                'total_trips' => 32,
                'total_earnings' => 1600000,
                'rating' => 4.8,
                'rating_count' => 32,
                'shift_start' => '07:00',
                'shift_end' => '16:00',
                'working_days' => json_encode(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Minggu'])
            ]);
        }
    }
}
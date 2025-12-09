<?php
// database/seeders/QueueTicketsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\QueueTicket;
use Carbon\Carbon;

class QueueTicketsSeeder extends Seeder
{
    public function run()
    {
        echo "Creating queue tickets...\n";
        
        // Get or create services
        $services = [
            'PICKUP_TRANSPORTASI' => [
                'name' => 'Pickup Transportasi',
                'sub_type' => 'Drop',
                'base_price' => 50000,
                'capacity' => 4
            ],
            'TRANSUP' => [
                'name' => 'Transup',
                'sub_type' => 'Tour',
                'base_price' => 100000,
                'capacity' => 6
            ],
            'SEWA_MOTOR' => [
                'name' => 'Sewa Motor',
                'sub_type' => '150 CC',
                'base_price' => 75000,
                'capacity' => 2
            ]
        ];
        
        foreach ($services as $type => $serviceData) {
            $service = Service::firstOrCreate(
                ['type' => $type],
                [
                    'name' => $serviceData['name'],
                    'sub_type' => $serviceData['sub_type'],
                    'base_price' => $serviceData['base_price'],
                    'capacity' => $serviceData['capacity'],
                    'is_active' => true
                ]
            );
            
            // Create waiting tickets
            $this->createWaitingTickets($service);
            
            // Create called tickets (for today)
            $this->createCalledTickets($service);
            
            // Create completed tickets (for today)
            $this->createCompletedTickets($service);
        }
        
        echo "Queue tickets created successfully!\n";
    }
    
    private function createWaitingTickets($service)
    {
        $prefix = $this->getQueuePrefix($service->type);
        
        for ($i = 1; $i <= rand(3, 8); $i++) {
            QueueTicket::create([
                'ticket_number' => 'TKT-' . date('Ymd') . '-' . str_pad($i + 100, 3, '0', STR_PAD_LEFT),
                'queue_number' => $prefix . str_pad($i, 3, '0', STR_PAD_LEFT),
                'service_id' => $service->id,
                'customer_name' => $this->getRandomName(),
                'customer_phone' => $this->getRandomPhone(),
                'passenger_count' => rand(1, $service->capacity),
                'pickup_location' => 'Pelabuhan Utama',
                'destination' => $this->getRandomDestination(),
                'notes' => rand(0, 1) ? 'Membawa bagasi besar' : null,
                'status' => 'WAITING',
                'created_at' => Carbon::now()->subMinutes(rand(5, 60)),
                'counter_id' => $this->getCounterIdByService($service->type)
            ]);
        }
    }
    
    private function createCalledTickets($service)
    {
        $prefix = $this->getQueuePrefix($service->type);
        
        for ($i = 1; $i <= rand(2, 4); $i++) {
            QueueTicket::create([
                'ticket_number' => 'TKT-' . date('Ymd') . '-' . str_pad($i + 200, 3, '0', STR_PAD_LEFT),
                'queue_number' => $prefix . str_pad($i + 10, 3, '0', STR_PAD_LEFT),
                'service_id' => $service->id,
                'customer_name' => $this->getRandomName(),
                'customer_phone' => $this->getRandomPhone(),
                'passenger_count' => rand(1, $service->capacity),
                'destination' => $this->getRandomDestination(),
                'status' => 'CALLED',
                'called_at' => Carbon::now()->subMinutes(rand(1, 30)),
                'counter_id' => $this->getCounterIdByService($service->type),
                'created_at' => Carbon::now()->subMinutes(rand(60, 120))
            ]);
        }
    }
    
    private function createCompletedTickets($service)
    {
        $prefix = $this->getQueuePrefix($service->type);
        
        for ($i = 1; $i <= rand(5, 10); $i++) {
            $calledAt = Carbon::now()->subMinutes(rand(60, 180));
            
            QueueTicket::create([
                'ticket_number' => 'TKT-' . date('Ymd') . '-' . str_pad($i + 300, 3, '0', STR_PAD_LEFT),
                'queue_number' => $prefix . str_pad($i + 20, 3, '0', STR_PAD_LEFT),
                'service_id' => $service->id,
                'customer_name' => $this->getRandomName(),
                'customer_phone' => $this->getRandomPhone(),
                'passenger_count' => rand(1, $service->capacity),
                'destination' => $this->getRandomDestination(),
                'status' => 'COMPLETED',
                'called_at' => $calledAt,
                'completed_at' => $calledAt->addMinutes(rand(5, 15)),
                'counter_id' => $this->getCounterIdByService($service->type),
                'created_at' => $calledAt->subMinutes(rand(10, 30))
            ]);
        }
    }
    
    private function getQueuePrefix($serviceType)
    {
        return match($serviceType) {
            'PICKUP_TRANSPORTASI' => 'P',
            'TRANSUP' => 'T',
            'SEWA_MOTOR' => 'M',
            default => 'Q'
        };
    }
    
    private function getCounterIdByService($serviceType)
    {
        return match($serviceType) {
            'PICKUP_TRANSPORTASI' => 1, // Loket 1
            'TRANSUP' => 2, // Loket 2
            'SEWA_MOTOR' => 3, // Loket 3
            default => 1
        };
    }
    
    private function getRandomName()
    {
        $firstNames = ['Budi', 'Siti', 'Agus', 'Dewi', 'Joko', 'Rina', 'Ahmad', 'Maya', 'Hendra', 'Linda'];
        $lastNames = ['Santoso', 'Wati', 'Prabowo', 'Sari', 'Wijaya', 'Putri', 'Fauzi', 'Kusuma', 'Setiawan', 'Lestari'];
        
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }
    
    private function getRandomPhone()
    {
        return '0812' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    }
    
    private function getRandomDestination()
    {
        $destinations = [
            'Hotel Marina',
            'Bandara Internasional',
            'Stasiun Kota',
            'Mall Plaza',
            'Rumah Sakit Umum',
            'Pantai Indah',
            'Pusat Kota',
            'Terminal Bus',
            'Kawasan Bisnis',
            'Perguruan Tinggi'
        ];
        
        return $destinations[array_rand($destinations)];
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update data yang rusak di counters table
        $counters = DB::table('counters')->get();
        
        foreach ($counters as $counter) {
            $serviceTypes = $counter->service_types;
            
            // Jika service_types sudah array, encode ke JSON
            if (is_array($serviceTypes)) {
                DB::table('counters')
                    ->where('id', $counter->id)
                    ->update([
                        'service_types' => json_encode($serviceTypes)
                    ]);
            }
            // Jika service_types string tapi bukan JSON valid, set ke default
            elseif (is_string($serviceTypes) && json_decode($serviceTypes) === null) {
                $defaultTypes = match($counter->code) {
                    'LOKET-1' => ['PICKUP_TRANSPORTASI'],
                    'LOKET-2' => ['TRANSUP'],
                    'LOKET-3' => ['SEWA_MOTOR'],
                    'LOKET-VIP' => ['PICKUP_TRANSPORTASI', 'TRANSUP', 'SEWA_MOTOR'],
                    default => []
                };
                
                DB::table('counters')
                    ->where('id', $counter->id)
                    ->update([
                        'service_types' => json_encode($defaultTypes)
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Tidak ada rollback yang diperlukan
    }
};
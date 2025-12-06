<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cek apakah column 'type' sudah ada
        if (!Schema::hasColumn('services', 'type')) {
            Schema::table('services', function (Blueprint $table) {
                $table->enum('type', ['PICKUP_TRANSPORTASI', 'TRANSUP', 'SEWA_MOTOR'])
                      ->default('PICKUP_TRANSPORTASI')
                      ->after('id')
                      ->comment('PICKUP_TRANSPORTASI, TRANSUP, SEWA_MOTOR');
            });
        }
        
        // Update existing records with type based on name
        $services = DB::table('services')->get();
        
        foreach ($services as $service) {
            $type = $this->determineTypeFromName($service->name);
            
            DB::table('services')
                ->where('id', $service->id)
                ->update(['type' => $type]);
        }
    }
    
    private function determineTypeFromName($name)
    {
        $name = strtolower($name);
        
        if (str_contains($name, 'pickup') || in_array($name, ['drop', 'tour', 'tirtayatre'])) {
            return 'PICKUP_TRANSPORTASI';
        } elseif (str_contains($name, 'transup')) {
            return 'TRANSUP';
        } elseif (str_contains($name, 'motor') || str_contains($name, 'cc')) {
            return 'SEWA_MOTOR';
        }
        
        return 'PICKUP_TRANSPORTASI'; // default
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
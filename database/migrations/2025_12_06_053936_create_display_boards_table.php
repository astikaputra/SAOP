<?php

// database/migrations/2024_01_01_000008_create_display_boards_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('display_boards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('location')->nullable();
            $table->enum('type', ['MAIN', 'WAITING_AREA', 'VIP', 'SERVICE_SPECIFIC'])->default('MAIN');
            $table->json('services_to_display')->nullable()->comment('Services to show on this board');
            $table->integer('refresh_rate')->default(10)->comment('Refresh rate in seconds');
            $table->boolean('show_current_calls')->default(true);
            $table->boolean('show_waiting_queues')->default(true);
            $table->boolean('show_statistics')->default(false);
            $table->string('api_key')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamps();
        });

        // Insert default display boards
        DB::table('display_boards')->insert([
            [
                'name' => 'Display Board Utama',
                'code' => 'DISPLAY-1',
                'location' => 'Lobi Utama',
                'type' => 'MAIN',
                'services_to_display' => json_encode(['PICKUP_TRANSPORTASI', 'TRANSUP', 'SEWA_MOTOR']),
                'refresh_rate' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Display Area Tunggu',
                'code' => 'DISPLAY-2',
                'location' => 'Area Tunggu',
                'type' => 'WAITING_AREA',
                'services_to_display' => json_encode(['PICKUP_TRANSPORTASI', 'TRANSUP', 'SEWA_MOTOR']),
                'refresh_rate' => 15,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Display Sewa Motor',
                'code' => 'DISPLAY-3',
                'location' => 'Area Sewa Motor',
                'type' => 'SERVICE_SPECIFIC',
                'services_to_display' => json_encode(['SEWA_MOTOR']),
                'refresh_rate' => 20,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('display_boards');
    }
};
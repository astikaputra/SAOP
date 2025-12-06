<?php

// database/migrations/2024_01_01_000002_create_counters_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counters', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('ip_address')->nullable();
            $table->foreignId('current_ticket_id')->nullable()->constrained('queue_tickets');
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'MAINTENANCE'])->default('ACTIVE');
            $table->enum('type', ['REGULAR', 'VIP', 'EXPRESS'])->default('REGULAR');
            $table->time('opening_time')->default('08:00:00');
            $table->time('closing_time')->default('17:00:00');
            $table->json('service_types')->nullable()->comment('Jenis layanan yang dilayani');
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Insert default counters
        DB::table('counters')->insert([
            [
                'code' => 'LOKET-1',
                'name' => 'Loket 1 - PICKUP',
                'location' => 'Area Utama Pelabuhan',
                'type' => 'REGULAR',
                'service_types' => json_encode(['PICKUP_TRANSPORTASI']),
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'LOKET-2',
                'name' => 'Loket 2 - TRANSUP',
                'location' => 'Area Utama Pelabuhan',
                'type' => 'REGULAR',
                'service_types' => json_encode(['TRANSUP']),
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'LOKET-3',
                'name' => 'Loket 3 - SEWA MOTOR',
                'location' => 'Area Parkir Motor',
                'type' => 'REGULAR',
                'service_types' => json_encode(['SEWA_MOTOR']),
                'display_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'LOKET-VIP',
                'name' => 'Loket VIP',
                'location' => 'Area VIP',
                'type' => 'VIP',
                'service_types' => json_encode(['PICKUP_TRANSPORTASI', 'TRANSUP', 'SEWA_MOTOR']),
                'display_order' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('counters');
    }
};
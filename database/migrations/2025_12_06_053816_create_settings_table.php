<?php

// database/migrations/2024_01_01_000006_create_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string')->comment('string, integer, boolean, json, array');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            // General Settings
            [
                'group' => 'general',
                'key' => 'app_name',
                'value' => 'Sistem Antrian Ojek Pelabuhan',
                'type' => 'string',
                'description' => 'Nama aplikasi',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'general',
                'key' => 'port_name',
                'value' => 'Pelabuhan Utama',
                'type' => 'string',
                'description' => 'Nama pelabuhan',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Queue Settings
            [
                'group' => 'queue',
                'key' => 'auto_call_interval',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Interval panggilan otomatis (menit)',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'queue',
                'key' => 'max_waiting_time',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Maksimal waktu tunggu (menit)',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'queue',
                'key' => 'no_show_timeout',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Waktu timeout no show (menit)',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Telegram Settings
            [
                'group' => 'telegram',
                'key' => 'bot_token',
                'value' => '',
                'type' => 'string',
                'description' => 'Token bot Telegram',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'telegram',
                'key' => 'channel_id',
                'value' => '',
                'type' => 'string',
                'description' => 'ID channel Telegram',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'telegram',
                'key' => 'notification_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Aktifkan notifikasi Telegram',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Printer Settings
            [
                'group' => 'printer',
                'key' => 'printer_type',
                'value' => 'thermal',
                'type' => 'string',
                'description' => 'Jenis printer',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'printer',
                'key' => 'printer_ip',
                'value' => '192.168.1.100',
                'type' => 'string',
                'description' => 'IP Address printer',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'printer',
                'key' => 'printer_port',
                'value' => '9100',
                'type' => 'integer',
                'description' => 'Port printer',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Business Hours
            [
                'group' => 'business',
                'key' => 'opening_time',
                'value' => '08:00',
                'type' => 'string',
                'description' => 'Jam buka',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'business',
                'key' => 'closing_time',
                'value' => '17:00',
                'type' => 'string',
                'description' => 'Jam tutup',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Display Settings
            [
                'group' => 'display',
                'key' => 'display_refresh_rate',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Refresh rate display board (detik)',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'group' => 'display',
                'key' => 'show_waiting_count',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Tampilkan jumlah antrian menunggu',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
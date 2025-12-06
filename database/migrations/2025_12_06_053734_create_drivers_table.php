<?php

// database/migrations/2024_01_01_000005_create_drivers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Driver Information
            $table->string('driver_id')->unique();
            $table->enum('vehicle_type', [
                'PICKUP_TRANSPORTASI',
                'TRANSUP',
                'MOTOR_110CC',
                'MOTOR_125CC',
                'MOTOR_150CC',
                'MOTOR_200CC',
                'MOTOR_250CC'
            ]);
            
            // Vehicle Information
            $table->string('vehicle_number')->nullable();
            $table->string('vehicle_brand')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->year('vehicle_year')->nullable();
            $table->string('vehicle_color')->nullable();
            
            // Driver Status
            $table->enum('status', [
                'AVAILABLE',
                'ON_TRIP',
                'BREAK',
                'OFFLINE',
                'MAINTENANCE'
            ])->default('OFFLINE');
            
            // Location Tracking
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->timestamp('location_updated_at')->nullable();
            
            // Statistics
            $table->integer('total_trips')->default(0);
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            
            // Documents
            $table->string('driver_license_image')->nullable();
            $table->string('vehicle_registration_image')->nullable();
            $table->string('insurance_image')->nullable();
            
            // Availability Schedule
            $table->time('shift_start')->nullable();
            $table->time('shift_end')->nullable();
            $table->json('working_days')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['vehicle_type', 'status']);
            $table->index(['status', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
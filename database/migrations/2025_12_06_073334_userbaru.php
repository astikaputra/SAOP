<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', [
                'SUPER_ADMIN',
                'ADMIN',
                'LOKET_STAFF', 
                'DRIVER',
                'MANAGER',
                'CUSTOMER'
            ])->default('CUSTOMER');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('photo')->nullable();
            $table->string('id_card_number')->nullable();
            $table->string('driver_license_number')->nullable();
            $table->foreignId('counter_id')->nullable()->constrained('counters')->onDelete('set null');
            $table->enum('counter_status', ['AVAILABLE', 'BUSY', 'BREAK', 'OFFLINE'])->default('OFFLINE');
            $table->timestamp('last_active_at')->nullable();
            $table->string('telegram_user_id')->nullable();
            $table->string('telegram_username')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('can_send_notifications')->default(true);
            $table->json('preferences')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
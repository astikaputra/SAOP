<?php

// database/migrations/2024_01_01_000003_create_queue_tickets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('counter_id')->nullable()->constrained()->onDelete('set null');
            
            // Customer Information
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_id_card')->nullable();
            
            // Trip Information
            $table->string('pickup_location')->nullable();
            $table->string('destination')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('passenger_count')->default(1);
            $table->integer('luggage_count')->default(0);
            $table->decimal('estimated_price', 12, 2)->nullable();
            $table->decimal('final_price', 12, 2)->nullable();
            
            // Queue Information
            $table->integer('queue_number')->comment('Nomor urut per layanan per hari');
            $table->enum('status', [
                'WAITING', 
                'CALLED', 
                'SERVING', 
                'COMPLETED', 
                'CANCELLED',
                'NO_SHOW',
                'TRANSFERRED'
            ])->default('WAITING');
            
            // Timing Information
            $table->timestamp('called_at')->nullable();
            $table->timestamp('serving_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Counter & Staff Information
            $table->foreignId('called_by')->nullable()->constrained('users');
            $table->foreignId('served_by')->nullable()->constrained('users');
            $table->foreignId('driver_id')->nullable()->constrained('users');
            
            // Telegram Integration
            $table->string('telegram_chat_id')->nullable();
            $table->string('telegram_message_id')->nullable();
            
            // Additional Information
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('priority_level')->default(0)->comment('0 = normal, 1 = priority, 2 = urgent');
            $table->boolean('is_vip')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['ticket_number']);
            $table->index(['service_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['counter_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_tickets');
    }
};
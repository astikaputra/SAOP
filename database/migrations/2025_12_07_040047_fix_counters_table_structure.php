<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('counters', function (Blueprint $table) {
            // Cek dan tambahkan kolom yang diperlukan
            if (!Schema::hasColumn('counters', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('set null');
            }
            
            if (!Schema::hasColumn('counters', 'current_ticket_id')) {
                $table->foreignId('current_ticket_id')->nullable()->after('user_id')->constrained('queue_tickets')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('counters', 'code')) {
                $table->string('code')->unique()->after('name');
            }
            
            if (!Schema::hasColumn('counters', 'status')) {
                $table->enum('status', ['ACTIVE', 'INACTIVE', 'MAINTENANCE'])->default('ACTIVE');
            }
            
            if (!Schema::hasColumn('counters', 'service_types')) {
                $table->json('service_types')->nullable();
            }
            
            if (!Schema::hasColumn('counters', 'location')) {
                $table->string('location')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('counters', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'current_ticket_id', 'code', 'status', 'service_types', 'location']);
        });
    }
};
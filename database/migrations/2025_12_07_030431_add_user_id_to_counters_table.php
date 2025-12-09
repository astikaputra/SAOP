<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('counters', function (Blueprint $table) {
            // Jika belum ada kolom user_id, tambahkan
            if (!Schema::hasColumn('counters', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            }
            
            // Tambahkan kolom lainnya jika diperlukan
            if (!Schema::hasColumn('counters', 'current_ticket_id')) {
                $table->foreignId('current_ticket_id')->nullable()->constrained('queue_tickets')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('counters', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            
            $table->dropForeign(['current_ticket_id']);
            $table->dropColumn('current_ticket_id');
        });
    }
};
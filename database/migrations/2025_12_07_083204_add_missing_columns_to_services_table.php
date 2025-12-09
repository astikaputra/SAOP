<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            // Tambahkan kolom yang belum ada
            if (!Schema::hasColumn('services', 'name')) {
                $table->string('name')->after('id');
            }
            
            if (!Schema::hasColumn('services', 'sub_type')) {
                $table->string('sub_type')->after('type');
            }
            
            if (!Schema::hasColumn('services', 'base_price')) {
                $table->decimal('base_price', 10, 2)->default(0)->after('sub_type');
            }
            
            if (!Schema::hasColumn('services', 'capacity')) {
                $table->integer('capacity')->default(1)->after('base_price');
            }
            
            if (!Schema::hasColumn('services', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('capacity');
            }
            
            // Optional: tambahkan kolom description jika diperlukan
            if (!Schema::hasColumn('services', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            
            // Optional: tambahkan kolom icon jika diperlukan
            if (!Schema::hasColumn('services', 'icon')) {
                $table->string('icon')->nullable()->after('description');
            }
            
            // Optional: tambahkan kolom estimated_time_minutes jika diperlukan
            if (!Schema::hasColumn('services', 'estimated_time_minutes')) {
                $table->integer('estimated_time_minutes')->nullable()->after('capacity');
            }
        });
    }

    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'sub_type', 
                'base_price',
                'capacity',
                'is_active',
                'description',
                'icon',
                'estimated_time_minutes'
            ]);
        });
    }
};
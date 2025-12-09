<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceDataToQueueTicketsTable extends Migration
{
    public function up()
    {
        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->json('service_data')->nullable()->after('service_id');
        });
    }

    public function down()
    {
        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->dropColumn('service_data');
        });
    }
}
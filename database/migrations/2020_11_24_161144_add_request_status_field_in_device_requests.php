<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestStatusFieldInDeviceRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('device_requests', function (Blueprint $table) {
            //
            $table->enum('request_status',['PENDING','PLEASE_COLLECT','APPROVED'])->default('PENDING');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_requests', function (Blueprint $table) {
            //
            $table->removeColumn('request_status');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsAssignDeliveryToDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->after('content')->comment('ステータス');
            $table->boolean('is_assign_date')->default(0)->after('status')->comment('納品日指定(0:false, 1:true)');
            $table->dateTime('assign_delivery_at')->nullable()->after('is_assign_date')->comment('指定納品日時');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'is_assign_date',
                'assign_delivery_at',
            ]);
        });
    }
}

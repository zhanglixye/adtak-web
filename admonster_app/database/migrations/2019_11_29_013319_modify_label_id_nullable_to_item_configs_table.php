<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyLabelIdNullableToItemConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('label_id')->after('item_no')->nullable()->comment('ラベルID')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('label_id')->after('item_no')->nullable(false)->comment('ラベルID')->change();
        });
    }
}

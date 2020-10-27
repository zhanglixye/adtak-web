<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyLabelIdNullableToItemConfigValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_config_values', function (Blueprint $table) {
            $table->unsignedBigInteger('label_id')->nullable()->comment('ラベルID')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_config_values', function (Blueprint $table) {
            $table->unsignedBigInteger('label_id')->nullable(false)->comment('ラベルID')->change();
        });
    }
}

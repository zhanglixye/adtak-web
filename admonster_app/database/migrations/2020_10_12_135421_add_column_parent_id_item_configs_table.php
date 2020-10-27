<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnParentIdItemConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_configs', function (Blueprint $table) {
            $table->bigInteger('parent_item_key')->nullable()->after('id')->comment('親ID');
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
            $table->dropColumn('parent_item_key');
        });
    }
}

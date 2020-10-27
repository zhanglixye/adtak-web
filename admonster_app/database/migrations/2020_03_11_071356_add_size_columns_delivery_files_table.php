<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSizeColumnsDeliveryFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_files', function (Blueprint $table) {
            $table->unsignedInteger('size')->after('file_path')->nullable()->comment('サイズ(byte)');
            $table->unsignedSmallInteger('width')->after('size')->nullable()->comment('幅(px)');
            $table->unsignedSmallInteger('height')->after('width')->nullable()->comment('高さ(px)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_files', function (Blueprint $table) {
            $table->dropColumn('size');
            $table->dropColumn('width');
            $table->dropColumn('height');
        });
    }
}

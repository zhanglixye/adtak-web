<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddItemKeyColumnToFileImportColumnConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file_import_column_configs', function (Blueprint $table) {
            $table->string('item_key', 256)->after('column')->comment('項目キー');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file_import_column_configs', function (Blueprint $table) {
            $table->dropColumn('item_key');
        });
    }
}

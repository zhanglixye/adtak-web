<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDisplayFormatOrderFileImportColumnConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_file_import_column_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('display_format')->default(0)->after('rule')->comment('表示形式');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_file_import_column_configs', function (Blueprint $table) {
            $table->dropColumn('display_format');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsToWorkTimeOperatorReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_time_operator_reports', function (Blueprint $table) {
            // nullを許可に変更
            $table->unsignedDecimal('manual_work_time', 6, 2)->nullable()->comment('手動工数(分で保持)')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_time_operator_reports', function (Blueprint $table) {
            // nullを許可しないに変更
            $table->unsignedDecimal('manual_work_time', 6, 2)->nullable(false)->comment('手動工数(分で保持)')->change();
        });
    }
}

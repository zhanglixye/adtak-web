<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSomeColumnsToTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_education')->default(0)->after('message')->comment('教育(0:false, 1:true)');
            $table->boolean('is_display_educational')->default(0)->after('is_education')->comment('教育表示(0:非表示, 1:表示)');
            $table->boolean('is_verified')->default(0)->after('status')->comment('検証済(0:false, 1:true)');
            $table->dateTime('deadline')->nullable()->after('is_display_educational')->comment('指定納期');
            $table->dateTime('system_deadline')->nullable()->after('deadline')->comment('システム納期');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('is_education');
            $table->dropColumn('is_display_educational');
            $table->dropColumn('is_verified');
            $table->dropColumn('deadline');
            $table->dropColumn('system_deadline');
        });
    }
}

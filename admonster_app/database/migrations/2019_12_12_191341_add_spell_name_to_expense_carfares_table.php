<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpellNameToExpenseCarfaresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expense_carfares', function (Blueprint $table) {
            $table->string('spell', 256)->nullable()->after('price')->comment('フリガナ');
            $table->string('name', 256)->nullable()->after('price')->comment('氏名');
        });
        DB::statement("ALTER TABLE expense_carfares modify column ap_accord TINYINT(4) comment 'AP(0:一致, 1:不一致)'");
        DB::statement("ALTER TABLE expense_carfares modify column have_station TINYINT(4) comment '有常駐(0:有, 1:無)'");
        DB::statement("ALTER TABLE expense_carfares modify column station_accord TINYINT(4) comment '常駐(0:一致, 1:不一致)'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expense_carfares', function (Blueprint $table) {
            $table->dropColumn('spell');
            $table->dropColumn('name');
        });
    }
}

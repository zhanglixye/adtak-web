<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpenseCarfaresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_carfares', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('task_id')->comment('タスクID');
            $table->unsignedBigInteger('step_id')->comment('作業ID');
            $table->unsignedBigInteger('request_work_id')->comment('依頼作業ID');
            $table->tinyInteger('expenses_type')->default(1)->comment('経費れつ(1:AP, 2:Station)');
            $table->string('employees_id', 256)->nullable()->comment('社員ID');
            $table->decimal('price', 14, 2)->nullable()->comment('申請合計金額');
            $table->char('date', 7)->nullable()->comment('会計年月');
            $table->tinyInteger('ap_accord')->nullable()->comment('AP(1:一致, 2:不一致)');
            $table->tinyInteger('have_station')->nullable()->comment('是否有常駐(1:有, 2:无)');
            $table->tinyInteger('station_accord')->nullable()->comment('常駐(1:一致, 2:不一致)');
            $table->string('ap_unprepared_unknown', 256)->nullable()->comment('AP的不明不備');
            $table->string('station_unprepared_unknown', 256)->nullable()->comment('常駐的不明不備');
            $table->string('unprepared_unknown', 256)->nullable()->comment('不明不備');
            $table->unique('task_id');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });
        // テーブルコメント定義
        DB::statement("ALTER TABLE expense_carfares COMMENT '交通費'");
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expense_carfares');
    }
}

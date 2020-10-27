<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskResultFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_result_files', function (Blueprint $table) {
            $table->unsignedBigInteger('task_result_id')->comment('タスク実績ID');
            $table->foreign('task_result_id')->references('id')->on('task_results')->onDelete('cascade');
            $table->unsignedBigInteger('seq_no')->comment('SeqNo');
            $table->string('name', 256)->nullable()->comment('ファイル名');
            $table->string('file_path', 256)->nullable()->comment('ファイルパス');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');

            //  キー
            $table->primary(['task_result_id', 'seq_no']);
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE task_result_files COMMENT 'タスク実績（ファイル）'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_result_files');
    }
}

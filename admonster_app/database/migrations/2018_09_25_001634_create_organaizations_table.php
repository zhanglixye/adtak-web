<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganaizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organaizations', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('company_id')->comment('企業ID');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('code', 256)->nullable()->comment('組織コード');
            $table->string('name', 256)->nullable()->comment('組織名');
            $table->bigInteger('short_name')->nullable()->comment('組織略称');
            $table->bigInteger('parent_id')->nullable()->comment('親組織ID');
            $table->smallInteger('display_order')->nullable()->comment('表示順');

            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE organaizations COMMENT '組織'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organaizations');
    }
}

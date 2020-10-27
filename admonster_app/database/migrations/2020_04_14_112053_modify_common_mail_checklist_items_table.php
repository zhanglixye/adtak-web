<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCommonMailChecklistItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('common_mail_checklist_items');
        Schema::create('common_mail_checklist_items', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('group_id')->comment('グループID');
            $table->foreign('group_id')->references('id')->on('common_mail_checklist_groups')->onDelete('cascade');
            $table->longText('content')->comment('コンテンツ')->nullable(true);
            $table->unsignedSmallInteger('component_type')->default(0)->comment('コンポーネントタイプ(0:チェックボックス,1:テキストボックス)');
            $table->unsignedSmallInteger('order_num')->default(0)->comment('順位');
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');
            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });
        // テーブルコメント定義
        DB::statement("ALTER TABLE common_mail_checklist_items COMMENT 'メール共通処理のチェックリスト項目'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('common_mail_checklist_items');
        Schema::create('common_mail_checklist_items', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('business_id')->comment('業務ID');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->unsignedBigInteger('company_id')->comment('企業ID');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedBigInteger('step_id')->comment('作業ID');
            $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade');
            $table->longText('content')->comment('コンテンツ')->nullable(true);
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');
            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
            $table->index(['company_id', 'business_id', 'step_id']);
        });
        // テーブルコメント定義
        DB::statement("ALTER TABLE common_mail_checklist_items COMMENT 'メール共通処理のチェックリスト項目'");
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->string('name', 256)->nullable()->comment('企業名');
            $table->string('name_kana', 256)->nullable()->comment('企業名カナ');
            $table->string('formal_name', 256)->nullable()->comment('正式名称');
            $table->tinyInteger('company_type')->nullable()->comment('企業種別');
            $table->string('postal_code', 16)->nullable()->comment('郵便番号');
            $table->string('address', 256)->nullable()->comment('住所');
            $table->string('tel', 16)->nullable()->comment('電話番号');
            $table->tinyInteger('billing_standard')->nullable()->comment('請求基準');
            $table->tinyInteger('billing_closing_day')->nullable()->comment('請求締め日');
            $table->tinyInteger('deposit_criteria')->nullable()->comment('入金基準');
            $table->tinyInteger('deposit_closing_day')->nullable()->comment('入金締め日');
            $table->string('bank', 256)->nullable()->comment('銀行名');
            $table->string('bank_branch', 256)->nullable()->comment('銀行支店名');
            $table->string('account_type', 2)->nullable()->comment('口座種別');
            $table->string('account_no', 8)->nullable()->comment('口座番号');
            $table->string('account_name', 256)->nullable()->comment('口座名');
            $table->longText('remarks')->nullable()->comment('備考');
            
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE companies COMMENT '企業'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}

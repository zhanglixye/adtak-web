<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyItemConfigsTable20191004 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_configs', function (Blueprint $table) {
            // change
            $table->smallInteger('item_no')->nullable(false)->change();
            $table->smallInteger('item_type')->default(null)->nullable()->change();
            $table->string('name', 256)->nullable(false)->comment('項目名(JSONのキーにあたる部分)')->change();
            $table->boolean('is_required')->default(0)->nullable(false)->change();

            // delete
            $table->dropColumn(['length', 'data_type']);

            // add column
            $table->unsignedBigInteger('label_id')->after('item_no')->comment('ラベルID');
            $table->boolean('is_deleted')->default(0)->after('is_required')->comment('削除(0:false, 1:true)');
            $table->longText('option')->nullable()->after('item_type')->comment('オプション(JSON形式でコンポーネント設定を定義)');
        });

        // changeの後にrenameを行うとカラム名は変更されるが、changeした内容が最終的に反映されなかったので分ける
        Schema::table('item_configs', function (Blueprint $table) {
            // rename
            $table->renameColumn('item_no', 'sort');
            $table->renameColumn('name', 'item_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_configs', function (Blueprint $table) {
            // delete column
            $table->dropColumn(['is_deleted', 'option', 'label_id']);

            //create
            $table->smallInteger('data_type')->after('item_type')->comment('データ型');
            $table->smallInteger('length')->after('data_type')->nullable()->comment('データ長');

            // change
            $table->smallInteger('sort')->default(null)->nullable(true)->change();
            $table->string('item_key', 256)->default(null)->nullable(true)->comment('項目名')->change();
            $table->boolean('is_required')->default(null)->nullable(true)->change();

            // Nullのデータがあるとエラーになるので、SQLなどで置換をかける必要あり
            $table->smallInteger('item_type')->nullable(false)->change();
        });

        // changeの後にrenameを行うとカラム名は変更されるが、changeした内容が最終的に反映されなかったので分ける
        Schema::table('item_configs', function (Blueprint $table) {
            // rename
            $table->renameColumn('sort', 'item_no');
            $table->renameColumn('item_key', 'name');
        });
    }
}

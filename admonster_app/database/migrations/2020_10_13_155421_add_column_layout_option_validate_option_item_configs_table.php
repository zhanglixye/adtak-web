<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLayoutOptionValidateOptionItemConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_configs', function (Blueprint $table) {
            $table->longText('layout_option')->nullable()->after('option')->comment('ページレイアウト(JSON形式でコンポーネント設定を定義)');
            $table->longText('validate_option')->nullable()->after('layout_option')->comment('データパターン(JSON形式でコンポーネント設定を定義)');
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
            $table->dropColumn('layout_option');
            $table->dropColumn('validate_option');
        });
    }
}

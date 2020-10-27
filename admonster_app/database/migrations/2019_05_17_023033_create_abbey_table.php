<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAbbeyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abbey', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('abbey_id')->comment('AbbeyID');
            $table->string('specification', 256)->comment('仕様 「メニュー名/サイズ（素材）」');
            $table->string('specification_2', 256)->nullable()->comment('仕様の小項目');
            $table->unsignedTinyInteger('purpose')->nullable()->comment('用途 「1：画像、2：動画（音声あり）、3：動画（音声なし）、4：代替画像、5：静止/代替画像、6：動画（音声必須）、７：画像（右パネル）、８：画像（左パネル）」');
            $table->unsignedSmallInteger('width')->nullable()->comment('ピクセルサイズ横');
            $table->unsignedSmallInteger('hight')->nullable()->comment('ピクセルサイズ縦');
            $table->float('file_size')->nullable()->comment('ファイルサイズ 「ファイルの最大サイズ」');
            $table->unsignedTinyInteger('file_size_unit')->nullable()->comment('ファイルサイズ単位 「1:KB 2:MB」');
            $table->string('file_format', 256)->nullable()->comment('ファイル形式「ビット表記:GIF89a,JPEG,PNG,MP4」');
            $table->string('total_bit_rate', 256)->nullable()->comment('総ビットレート');
            $table->string('animation', 256)->nullable()->comment('アニメーション 「単位は"秒"」');
            $table->string('alt_text', 256)->nullable()->comment('ALTテキスト');
            $table->string('link', 256)->nullable()->comment('リンク先');
            $table->string('target_Loudness', 256)->nullable()->comment('ターゲットラウンドネス値');
            $table->string('text', 256)->nullable()->comment('テキスト');
            $table->string('title_text', 256)->nullable()->comment('タイトルテキスト');
            $table->string('branding_text', 256)->nullable()->comment('ブランディングテキスト（主体者表記）');
            $table->string('search_full_text', 1000)->nullable()->comment('検索用（仕様）');
            $table->string('search_full_text_2', 1000)->nullable()->comment('検索用（仕様の小項目）');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE abbey COMMENT 'Abbeyマスターデータ'");
        DB::statement("ALTER TABLE `abbey` ADD FULLTEXT KEY `idx_abbey_1` (`search_full_text`,`search_full_text_2`)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('abbey');
    }
}

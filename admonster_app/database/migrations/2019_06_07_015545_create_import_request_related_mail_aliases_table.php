<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportRequestRelatedMailAliasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_request_related_mail_aliases', function (Blueprint $table) {
            $table->unsignedBigInteger('request_id')->comment('依頼ID');
            $table->foreign('request_id')->references('id')->on('requests')->onDelete('cascade');
            $table->string('alias', 256)->comment('エイリアス');
            
            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_request_related_mail_aliases');
    }
}

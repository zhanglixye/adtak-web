<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsOpenToClientToImportRequestRelatedMailAliasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_request_related_mail_aliases', function (Blueprint $table) {
            $table->boolean('is_open_to_client')->default(0)->after('alias')->comment('クライアントへの公開(0:false, 1:true)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import_request_related_mail_aliases', function (Blueprint $table) {
            $table->dropColumn('is_open_to_client');
        });
    }
}

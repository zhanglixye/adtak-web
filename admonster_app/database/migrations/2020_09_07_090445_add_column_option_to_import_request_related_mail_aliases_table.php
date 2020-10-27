<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnOptionToImportRequestRelatedMailAliasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_request_related_mail_aliases', function (Blueprint $table) {
            $table->longText('from')->nullable()->after('is_open_to_work')->comment('送信元');
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
            $table->dropColumn('from');
        });
    }
}

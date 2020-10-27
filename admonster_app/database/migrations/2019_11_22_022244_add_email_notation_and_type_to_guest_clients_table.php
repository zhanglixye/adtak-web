<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailNotationAndTypeToGuestClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guest_clients', function (Blueprint $table) {
            $table->string('email_notation', 256)->after('email')->comment('メールアドレス表記');
            $table->tinyInteger('type')->nullable()->after('email_notation')->comment('クライアント種別(1:依頼メールfrom, 2:依頼メールcc, 3:依頼メールbcc)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guest_clients', function (Blueprint $table) {
            $table->dropColumn('email_notation');
            $table->dropColumn('type');
        });
    }
}

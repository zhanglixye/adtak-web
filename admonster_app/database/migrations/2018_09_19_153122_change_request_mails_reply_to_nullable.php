<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeRequestMailsReplyToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_mails', function (Blueprint $table) {
            // reply_toカラムにNULLを許容
            $table->text('reply_to')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_mails', function (Blueprint $table) {
            
            $table->text('reply_to')->nullable(false)->change();
        });
    }
}

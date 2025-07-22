<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemindedBesokToPiutangInstallements extends Migration
{
    public function up()
    {
        Schema::table('piutang_installements', function (Blueprint $table) {
            $table->tinyInteger('reminded_besok')->default(0)->after('is_paid');
        });
    }

    public function down()
    {
        Schema::table('piutang_installements', function (Blueprint $table) {
            $table->dropColumn('reminded_besok');
        });
    }
}

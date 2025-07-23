<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOwnerPhoneToPiutangsTable extends Migration
{
    public function up()
    {
        Schema::table('piutangs', function (Blueprint $table) {
            $table->string('owner_phone')->nullable()->after('payment_from');
        });
    }

    public function down()
    {
        Schema::table('piutangs', function (Blueprint $table) {
            $table->dropColumn('owner_phone');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('piutangs', function (Blueprint $table) {
            $table->string('collection_id')->primary();
            $table->string('moneyin_id');
            $table->foreign('moneyin_id')->references('trx_id')->on('money_ins')->onDelete('cascade');
            $table->string('amount');
            $table->string('ext_doc_ref')->nullable();
            $table->string('payment_from')->nullable();
            $table->datetime('due_date');
            $table->longText('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piutangs');
    }
};

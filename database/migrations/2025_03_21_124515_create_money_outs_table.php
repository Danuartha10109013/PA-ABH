<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('money_outs', function (Blueprint $table) {
            $table->string('trx_id')->primary();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('payments')->onDelete('cascade');
            $table->integer('amount');
            $table->integer('payment_method');
            $table->string('proof')->nullable();
            $table->string('ext_doc_ref')->nullable();
            $table->string('payment_to')->nullable();
            $table->datetime('payment_date');
            $table->longText('note')->nullable();
            $table->decimal('tax',5,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('money_outs');
    }
};

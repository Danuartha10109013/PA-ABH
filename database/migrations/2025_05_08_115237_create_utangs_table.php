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
        Schema::create('utangs', function (Blueprint $table) {
            $table->string('trx_id')->primary();
            $table->string('moneyout_id');
            $table->foreign('moneyout_id')->references('trx_id')->on('money_outs')->onDelete('cascade');
            $table->string('amount');
            $table->string('ext_doc_ref')->nullable();
            $table->string('payment_from')->nullable();
            $table->datetime('due_date');
            $table->boolean('is_paid')->default(false);
            $table->longText('note')->nullable();
            $table->string('type')->default('full');
            $table->integer('installment_count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utangs');
    }
};

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
        Schema::create('piutang_installements', function (Blueprint $table) {
            $table->id();
            $table->string('piutang_collection_id');
            $table->foreign('piutang_collection_id')->references('collection_id')->on('piutangs')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piutang_installements');
    }
}; 
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
        Schema::create('call_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->longText('transcription')->nullable();
            $table->json('extraction')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_metas');
    }
};

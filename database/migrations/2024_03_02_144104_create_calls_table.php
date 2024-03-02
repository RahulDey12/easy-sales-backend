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
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained();
            $table->foreignId('caller_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('title');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->longText('requirement');
            $table->string('phone_number');
            $table->string('twilio_sid')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('validation_history', function (Blueprint $table) {
            $table->id('validation_id');
            $table->unsignedBigInteger('partner_id');
            $table->unsignedBigInteger('user_id'); // admin atau validator
            $table->timestamp('validation_date')->nullable();
            $table->string('status', 20)->default('pending'); // pending, terverifikasi, tolak
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('partner_id')->references('partner_id')->on('batik_umkm_partner')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_history');
    }
};

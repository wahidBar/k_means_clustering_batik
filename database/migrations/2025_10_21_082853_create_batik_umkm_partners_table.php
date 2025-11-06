<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batik_umkm_partner', function (Blueprint $table) {
            $table->id('partner_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('business_name', 50);
            $table->string('owner_name', 30);
            $table->text('address');
            $table->json('pemasaran')->nullable();
            $table->string('contact', 16)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('nib', 30); // Nomor Induk Berusaha
            $table->string('images_partner'); // Simpan path gambar
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->string('validation_status', 20)->default('Pending');
            $table->string('cluster', 10)->nullable(); // hasil clustering K-Means
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batik_umkm_partner');
    }
};

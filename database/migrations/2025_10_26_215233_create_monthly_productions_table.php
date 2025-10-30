<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_production', function (Blueprint $table) {
            $table->id('production_id');
            $table->unsignedBigInteger('partner_id');
            $table->unsignedBigInteger('product_id');
            $table->string('month', 10);
            $table->integer('total_quantity')->nullable();
            $table->text('production_notes')->nullable();
            $table->string('validation_status', 20)->default('pending'); // pending, approved, rejected
            $table->unsignedBigInteger('validated_by')->nullable(); // user_id admin validator
            $table->timestamp('validation_date')->nullable();
            $table->string('cluster', 10)->nullable(); // hasil clustering K-Means
            $table->timestamps();

            // Relasi ke partner, product, dan user validator
            $table->foreign('partner_id')->references('partner_id')->on('batik_umkm_partner')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('batik_products')->onDelete('cascade');
            $table->foreign('validated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_production');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('batik_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id'); // harus unsigned dan cocok tipe-nya

            $table->foreign('partner_id')
                ->references('partner_id')
                ->on('batik_umkm_partner')
                ->onDelete('cascade');

            $table->foreignId('type_id')->constrained('types')->onDelete('restrict');
            $table->string('product_name', 50);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batik_products');
    }
};

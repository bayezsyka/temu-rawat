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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nomor_whatsapp', 30);
            $table->date('tanggal_lahir')->nullable();
            $table->unsignedTinyInteger('usia')->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->string('alamat')->nullable();
            $table->timestamps();

            $table->index('nomor_whatsapp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};

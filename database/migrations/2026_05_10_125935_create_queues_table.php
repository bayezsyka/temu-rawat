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
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('practice_session_id')->constrained()->cascadeOnDelete();
            $table->string('kode_antrian')->unique();
            $table->unsignedInteger('nomor_urut');
            $table->text('keluhan')->nullable();
            $table->string('status_kunjungan', 20);
            $table->string('metode_daftar', 20);
            $table->string('status', 20)->default('menunggu');
            $table->timestamp('waktu_daftar');
            $table->timestamp('waktu_dipanggil')->nullable();
            $table->timestamp('waktu_mulai_periksa')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->timestamps();

            $table->unique(['practice_session_id', 'nomor_urut']);
            $table->index(['practice_session_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};

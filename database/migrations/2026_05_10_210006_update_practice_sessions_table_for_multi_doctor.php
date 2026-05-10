<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practice_sessions', function (Blueprint $table) {
            $table->dropUnique('practice_sessions_tanggal_unique');

            $table->foreignId('doctor_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
            $table->timestamp('mulai_pada')->nullable()->after('nomor_terakhir');
            $table->timestamp('selesai_pada')->nullable()->after('mulai_pada');

            $table->index(['doctor_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::table('practice_sessions', function (Blueprint $table) {
            $table->dropIndex('practice_sessions_doctor_id_tanggal_index');
            $table->dropConstrainedForeignId('doctor_id');
            $table->dropColumn(['mulai_pada', 'selesai_pada']);
            $table->unique('tanggal');
        });
    }
};

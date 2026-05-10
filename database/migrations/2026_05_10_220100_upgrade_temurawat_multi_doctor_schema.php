<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practice_sessions', function (Blueprint $table) {
            $table->unique(['doctor_id', 'tanggal'], 'practice_sessions_doctor_id_tanggal_unique');
        });

        Schema::table('queues', function (Blueprint $table) {
            $table->string('public_code', 40)->nullable()->after('practice_session_id');
            $table->dropUnique('queues_kode_antrian_unique');
            $table->unique(['practice_session_id', 'kode_antrian'], 'queues_session_code_unique');
            $table->unique('public_code');
        });

        DB::table('queues')->orderBy('id')->get()->each(function ($queue): void {
            DB::table('queues')
                ->where('id', $queue->id)
                ->update(['public_code' => (string) Str::ulid()]);
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->unique('medical_visit_id');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropUnique(['medical_visit_id']);
        });

        Schema::table('queues', function (Blueprint $table) {
            $table->dropUnique('queues_session_code_unique');
            $table->dropUnique(['public_code']);
            $table->unique('kode_antrian');
            $table->dropColumn('public_code');
        });

        Schema::table('practice_sessions', function (Blueprint $table) {
            $table->dropUnique('practice_sessions_doctor_id_tanggal_unique');
        });
    }
};

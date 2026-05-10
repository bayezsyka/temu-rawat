<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('initial_checks', function (Blueprint $table) {
            $table->unsignedSmallInteger('saturasi_oksigen')
                ->nullable()
                ->after('nadi');
            $table->foreignId('checked_by')
                ->nullable()
                ->after('catatan_asisten')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('initial_checks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('checked_by');
            $table->dropColumn('saturasi_oksigen');
        });
    }
};

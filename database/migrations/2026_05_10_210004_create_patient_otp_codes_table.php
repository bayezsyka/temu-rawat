<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_account_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('otp_hash');
            $table->timestamp('expired_at');
            $table->timestamp('verified_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();

            $table->index(['patient_account_id', 'expired_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_otp_codes');
    }
};

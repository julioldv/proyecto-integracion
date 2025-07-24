<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('key_pairs', function (Blueprint $table) {
            $table->id();

            // Si luego cambias a certificado X.509,
            // simplemente renombra esta columna a certificate_pem
            $table->text('public_key');

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->timestamps();

            /* 🔑 Un único par por usuario */
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('key_pairs');
    }
};

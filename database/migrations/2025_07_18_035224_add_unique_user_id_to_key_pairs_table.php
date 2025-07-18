<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\KeyPair;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Elimina llaves duplicadas dejando solo la más reciente
        KeyPair::select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id')
            ->each(function ($uid) {
                KeyPair::where('user_id', $uid)
                       ->orderByDesc('id')           // conserva la última
                       ->skip(1)                     // borra el resto
                       ->take(PHP_INT_MAX)
                       ->delete();
            });

        // 2) Añade el índice único
        Schema::table('key_pairs', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('key_pairs', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};

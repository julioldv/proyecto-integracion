<?php
/*───────────────────────────────────────────────────────────────
 |  Tabla: documents
 |  Motivo: Persistir meta-datos de cada PDF subido.
 |  Regla de negocio clave:
 |    • hash SHA-256 se genera al momento de la subida y nunca cambia.
 |    • Si el usuario se elimina, los documentos se borran en cascada.
 *──────────────────────────────────────────────────────────────*/
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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('original_name'); // Nombre del archivo original
            $table->string('file_path');     // Ruta donde se guarda el archivo
            $table->string('file_hash');     // Hash SHA-256 del documento
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relación con User
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

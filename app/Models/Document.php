<?php
/**
 * Modelo Eloquent: Document
 * -------------------------
 *  • Representa la tabla `documents`
 *  • Encapsula la lógica de verificación de firmas
 *  • Relaciones:
 *      - belongsTo User   → dueño que subió el PDF
 *      - hasMany  Signature → firmas digitales asociadas
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\KeyPair;
use App\Models\User;
use App\Models\Signature;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_name',
        'file_path',
        'file_hash',
        'user_id',
    ];

    //Relaciones
    public function signatures() { return $this->hasMany(Signature::class); }
    public function user() { return $this->belongsTo(User::class); }

    //Estado de la fima para un usuario
    public function signatureStatusFor(User $user): string   // 
    {   
        //0. Verificamos integridad del archivo
        if (!$this->isIntact()) {
        return 'alterado';          // ⚠ archivo no coincide con hash BD
        }

        //1. Verificamos si existe firma
        $sig = $this->signatures()->where('user_id', $user->id)->first();

        if (!$sig) {
            return 'no-firmado';
        }

        //2. Obtener la llave pública usada para esa firma
        $publicKey = KeyPair::where('user_id', $user->id)
                            ->latest()
                            ->value('public_key');

        if (!$publicKey) {
            return 'sin-publica';      // caso anómalo
        }

        //3. Verificar la firma
        $ok = openssl_verify(
            $this->file_hash,
            base64_decode($sig->signature_bin),
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        return $ok === 1 ? 'válida' : 'inválida';
    }

    /**
     * Devuelve true si el archivo físico coincide con el hash
     * almacenado en la base de datos.
     */
    public function isIntact(): bool
    {
        $path = storage_path('app/public/' . $this->file_path);

        return file_exists($path) &&
            hash_file('sha256', $path) === $this->file_hash;
    }

}
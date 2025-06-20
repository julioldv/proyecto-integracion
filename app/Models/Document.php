<?php

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

    public function signatures() { return $this->hasMany(Signature::class); }

    public function signatureStatusFor(User $user): string   // ← cambia el tipo
    {
        $sig = $this->signatures()->where('user_id', $user->id)->first();

        if (!$sig) {
            return 'no-firmado';
        }

        // llave pública más reciente del firmante
        $publicKey = KeyPair::where('user_id', $user->id)
                            ->latest()
                            ->value('public_key');

        if (!$publicKey) {
            return 'sin-publica';      // caso anómalo
        }

        $ok = openssl_verify(
            $this->file_hash,
            base64_decode($sig->signature_bin),
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        return $ok === 1 ? 'válida' : 'inválida';
    }
}

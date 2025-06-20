<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;     // ← fachada Auth
use App\Models\Document;                 // ← modelo de documentos
use App\Models\KeyPair;                  // ← modelo de llaves públicas
use App\Models\Signature;                // ← modelo de firmas

class SignatureController extends Controller
{
    public function store(Request $request, Document $document)
    {
        // 1. Validar que el usuario tenga al menos una llave pública
        $publicKey = KeyPair::where('user_id', Auth::id())
                            ->latest()
                            ->value('public_key');

        if (!$publicKey) {
            return back()->with('error', 'Primero genera una llave pública.');
        }

        // 2. Recibir la llave privada que el usuario sube
        $request->validate([
            'private_key' => 'required|file|mimetypes:text/plain,text/x-pem-file',
        ]);
        $privatePem = file_get_contents(
            $request->file('private_key')->getRealPath()
        );

        // 3. Comprobar que la llave privada coincide con la pública guardada
        $testData = 'ping';
        openssl_sign($testData, $sig, $privatePem, OPENSSL_ALGO_SHA256);
        $ok = openssl_verify($testData, $sig, $publicKey, OPENSSL_ALGO_SHA256);

        if ($ok !== 1) {
            return back()->with('error',
                'La llave privada no coincide con tu llave pública almacenada.');
        }

        // 4. Firmar el hash del documento
        $signature = null;
        openssl_sign(
            $document->file_hash,
            $signature,
            $privatePem,
            OPENSSL_ALGO_SHA256
        );

        // 5. Guardar (o actualizar) la firma en BD
        Signature::updateOrCreate(
            ['document_id' => $document->id, 'user_id' => Auth::id()],
            ['signature_bin' => base64_encode($signature)]
        );

        return back()->with('success', 'Documento firmado correctamente.');
    }
}

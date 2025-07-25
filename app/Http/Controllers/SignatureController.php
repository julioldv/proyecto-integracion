<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Document;
use App\Models\KeyPair;
use App\Models\Signature;

class SignatureController extends Controller
{
    public function store(Request $request, Document $document)
    {
        /* 0 · Integridad del PDF */
        if (!$document->isIntact()) {                         // usa el helper del modelo
            return back()->with('error',
                'El archivo almacenado fue modificado; no se puede firmar.');
        }

        /* 1 · Obtener el par de llaves (único) del usuario */
        $keyPair = Auth::user()->keyPairs()->latest()->first();
        if (!$keyPair) {
            return back()->with('error', 'Primero genera tu par de llaves.');
        }

        /* 2 · Recibir la llave privada que sube el usuario */
        $request->validate([
            'private_key' => 'required|file|mimetypes:text/plain,text/x-pem-file',
        ]);
        $privatePem = file_get_contents(
            $request->file('private_key')->getRealPath()
        );

        /* 3 · Comprobar que la privada coincide con la pública almacenada */
        openssl_sign('ping', $sigTest, $privatePem, OPENSSL_ALGO_SHA256);
        $ok = openssl_verify('ping', $sigTest, $keyPair->public_key, OPENSSL_ALGO_SHA256);
        if ($ok !== 1) {
            return back()->with('error',
                'La llave privada no coincide con tu llave pública almacenada.');
        }

        /* 4 · Firmar el hash del documento */
        openssl_sign(
            $document->file_hash,
            $signatureBin,
            $privatePem,
            OPENSSL_ALGO_SHA256
        );

        /* 5 · Guardar (o actualizar) la firma y enlazarla con key_pair_id */
        Signature::updateOrCreate(
            ['document_id' => $document->id, 'user_id' => Auth::id()],
            [
                'key_pair_id'  => $keyPair->id,
                'signature_bin'=> base64_encode($signatureBin),
            ]
        );

        return back()->with('success', 'Documento firmado correctamente.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\KeyPair;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeyPairController extends Controller
{
    /* ───────── Listado ───────── */
    public function index()
    {
        $keys = KeyPair::where('user_id', Auth::id())->get(); // ya será solo 1
        return view('keys.index', compact('keys'));
    }

    /* ───────── Generar / Regenerar par de llaves ───────── */
    public function store(Request $request)
    {
        /* 1. Generar par RSA‑2048 */
        $cfg = [
            'private_key_bits'  => 2048,
            'private_key_type'  => OPENSSL_KEYTYPE_RSA,
        ];
        $res = openssl_pkey_new($cfg);

        if ($res === false) {
            return back()->with(
                'error',
                'OpenSSL no pudo generar la llave: ' . openssl_error_string()
            );
        }

        /* 2. Extraer llaves en PEM */
        openssl_pkey_export($res, $privatePem);
        $publicPem = openssl_pkey_get_details($res)['key'];

        /* 3. Política: un solo par por usuario →  
              - borrar firmas del usuario  
              - borrar (o sobrescribir) llave anterior */
        Signature::where('user_id', Auth::id())->delete();
        KeyPair::where('user_id', Auth::id())->delete();

        $keyPair = KeyPair::create([
            'public_key' => $publicPem,
            'user_id'    => Auth::id(),
        ]);

        /* 4. Descargar la privada una única vez */
        return response()->streamDownload(
            fn() => print($privatePem),
            'private_key_' . $keyPair->id . '.pem'
        );
    }
}

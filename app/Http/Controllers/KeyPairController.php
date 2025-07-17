<?php

namespace App\Http\Controllers;

use App\Models\KeyPair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeyPairController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $keys = KeyPair::where('user_id', Auth::id())->latest()->get();
        return view('keys.index', compact('keys'));
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     return view('keys.create');
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /// 1. Generar par de llaves
        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        $res = openssl_pkey_new($config);

        // 2. Extraer llaves
        openssl_pkey_export($res, $privateKey);                       // PEM de la llave privada
        $publicKey = openssl_pkey_get_details($res)['key'];           // PEM de la pública

        // 3. Guardar la pública en BD
        $keyPair = KeyPair::create([
            'public_key' => $publicKey,
            'user_id'    => Auth::id(),
        ]);

        // 4. Entregar la privada como descarga única
        return response()->streamDownload(
            fn() => print($privateKey),
            'private_key_'.$keyPair->id.'.pem'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(KeyPair $keyPair)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KeyPair $keyPair)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KeyPair $keyPair)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KeyPair $keyPair)
    {
        //
    }
}
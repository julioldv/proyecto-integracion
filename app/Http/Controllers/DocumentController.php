<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;
use App\Models\KeyPair;       // ←  nuevo
use App\Models\Signature;     // (por si quieres usarlo en show)

class DocumentController extends Controller
{
    /* ───── Lista ───── */
    public function index(Request $request)
    {
        $query = Document::where('user_id', Auth::id());

        if ($request->has('search') && $request->search !== '') {
            $query->where('original_name', 'like', '%' . $request->search . '%');
        }

        $documents = $query->latest()->get();

        return view('documents.index', compact('documents'));
    }

    /* ───── Formulario de subida ───── */
    public function create()
    {
        return view('documents.create');
    }

    /* ───── Guardar PDF ───── */
    public function store(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf|max:10240',
        ]);

        $file         = $request->file('document');
        $originalName = $file->getClientOriginalName();
        $hash         = hash_file('sha256', $file->getRealPath());
        $path         = $file->store('documents', 'public');

        Document::create([
            'original_name' => $originalName,
            'file_path'     => $path,
            'file_hash'     => $hash,
            'user_id'       => Auth::id(),
        ]);

        return redirect()
            ->route('documents.index')
            ->with('success', 'Documento subido correctamente.');
    }

    /* ───── Mostrar  ───── */
    public function show(Document $document)
    {
        $signature = $document->signatures()
                              ->where('user_id', Auth::id())
                              ->first();

        $isValid = null;
        if ($signature) {
            $publicKey = $signature->user->keyPairs()->latest()->value('public_key');
            $isValid   = openssl_verify(
                $document->file_hash,
                base64_decode($signature->signature_bin),
                $publicKey,
                OPENSSL_ALGO_SHA256
            ) === 1;
        }

        // Podria retornarse una vista con $document y $isValid
    }

    /* ───── Eliminar PDF ───── */
    public function destroy(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()
            ->route('documents.index')
            ->with('success', 'Documento eliminado correctamente.');
    }

    /* ───── Descargar con verificación de firma ───── */
    public function download(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->route('documents.index')
                             ->with('error', 'El archivo ya no existe en el sistema.');
        }

        /* 1. Verificar la firma (si existe) */
        $sign = $document->signatures()
                         ->where('user_id', Auth::id())
                         ->first();

        if ($sign) {
            $publicKey = KeyPair::where('user_id', Auth::id())
                                ->latest()
                                ->value('public_key');

            $ok = openssl_verify(
                $document->file_hash,
                base64_decode($sign->signature_bin),
                $publicKey,
                OPENSSL_ALGO_SHA256
            );

            if ($ok !== 1) {
                return back()->with(
                    'error',
                    'Firma inválida: el documento pudo haberse alterado.'
                );
            }
        }

        /* 2. Todo bien → descarga */
        return Storage::disk('public')->download(
            $document->file_path,
            $document->original_name
        );
    }
}

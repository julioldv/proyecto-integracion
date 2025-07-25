<?php
/**
 * Controlador para Documentos
 * ---------------------------------------------------------
 *  index()      → Listado global + búsqueda (nombre / correo)
 *  create()     → Formulario para subir PDF
 *  store()      → Guarda PDF y metadatos
 *  show()       → Detalle + estado de TODAS las firmas
 *  download()   → Descarga (verifica integridad + firma del dueño)
 *  destroy()    → Borra registro + archivo (solo dueño)
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;
use App\Models\KeyPair;
use App\Models\Signature;

class DocumentController extends Controller
{
    /* ───────── Listado + búsqueda ───────── */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $documents = Document::query()
            ->with('user')                                              // → mostrar propietario
            ->when($search, function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) =>
                      $u->where('email', 'like', "%{$search}%")
                  );
            })
            ->latest()
            ->get();

        return view('documents.index', compact('documents', 'search'));
    }

    /* ───────── Formulario ───────── */
    public function create()
    {
        return view('documents.create');
    }

    /* ───────── Guardar PDF ───────── */
    public function store(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf|max:10240',
        ]);

        $file  = $request->file('document');

        Document::create([
            'original_name' => $file->getClientOriginalName(),
            'file_path'     => $file->store('documents', 'public'),
            'file_hash'     => hash_file('sha256', $file->getRealPath()),
            'user_id'       => Auth::id(),
        ]);

        return redirect()
            ->route('documents.index')
            ->with('success', 'Documento subido correctamente.');
    }

    /* ───────── Detalle: firmas + estado ───────── */
    public function show(Document $document)
    {
        // obtenemos todas las firmas + su key pair + usuario firmante
        $signatures = $document->signatures()
                               ->with(['user', 'keyPair'])
                               ->get()
                               ->map(function (Signature $sig) use ($document) {
                                   $publicKey = $sig->keyPair->public_key ?? null;

                                   $valid = $publicKey
                                       ? openssl_verify(
                                             $document->file_hash,
                                             base64_decode($sig->signature_bin),
                                             $publicKey,
                                             OPENSSL_ALGO_SHA256
                                         ) === 1
                                       : null;  // sin llave → no se puede validar

                                   return [
                                       'signature' => $sig,
                                       'valid'     => $valid,
                                   ];
                               });

        return view('documents.show', compact('document', 'signatures'));
    }

    /* ───────── Eliminar (solo dueño) ───────── */
    public function destroy(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            return back()->with('error', 'Solo el propietario puede eliminar el documento.');
        }

        /* borra archivo físico si existe */
        Storage::disk('public')->delete($document->file_path);

        $document->delete();

        return back()->with('success', 'Documento eliminado correctamente.');
    }

    /* ───────── Descargar ───────── */
    public function download(Document $document)
    {
        /** 1 · Integridad */
        if (!$document->isIntact()) {
            return back()->with(
                'error',
                'El archivo fue alterado o falta; descarga cancelada.'
            );
        }

        /** 2 · Verifica firma del propietario (si existe) */
        $sign = $document->signatures()
                         ->where('user_id', $document->user_id)
                         ->first();

        if ($sign) {
            $publicKey = $sign->keyPair->public_key ?? null;

            if (!$publicKey ||
                openssl_verify(
                    $document->file_hash,
                    base64_decode($sign->signature_bin),
                    $publicKey,
                    OPENSSL_ALGO_SHA256
                ) !== 1) {

                return back()->with('error', 'Firma inválida; el documento no coincide.');
            }
        }

        /** 3 · Todo correcto → descarga */
        return Storage::disk('public')->download(
            $document->file_path,
            $document->original_name
        );
    }
}

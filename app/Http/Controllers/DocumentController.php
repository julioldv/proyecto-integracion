<?php
/**
 * Controlador para Documentos
 * ---------------------------------------------------------
 *  index()      → Listado global + búsqueda (nombre / correo)
 *  create()     → Formulario para subir PDF
 *  store()      → Guarda PDF y metadatos
 *  download()   → Descarga (verifica integridad + firma)
 *  destroy()    → Borra registro + archivo (solo dueño)
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;
use App\Models\KeyPair;

class DocumentController extends Controller
{
    /* ───────── Listado + búsqueda ───────── */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $documents = Document::query()
            ->with('user')                                              // mostrar propietario
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
        $hash  = hash_file('sha256', $file->getRealPath());
        $path  = $file->store('documents', 'public');

        Document::create([
            'original_name' => $file->getClientOriginalName(),
            'file_path'     => $path,
            'file_hash'     => $hash,
            'user_id'       => Auth::id(),
        ]);

        return redirect()->route('documents.index')
                         ->with('success', 'Documento subido correctamente.');
    }

    /* ───────── Eliminar (solo dueño) ───────── */
    public function destroy(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            return back()->with('error', 'Solo el propietario puede eliminar el documento.');
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Documento eliminado correctamente.');
    }

    /* ───────── Descargar ───────── */
    public function download(Document $document)
    {
        /** 1 · Integridad del archivo */
        if (!$this->checkIntegrity($document)) {
            return back()->with('error', 'El archivo fue alterado o falta; descarga cancelada.');
        }

        /** 2 · Verificación de firma (si existe) */
        $sign = $document->signatures()
                         ->where('user_id', $document->user_id)
                         ->first();                                 // firma del propietario

        if ($sign) {
            $publicKey = KeyPair::where('user_id', $document->user_id)
                                ->latest()
                                ->value('public_key');

            $ok = openssl_verify(
                $document->file_hash,
                base64_decode($sign->signature_bin),
                $publicKey,
                OPENSSL_ALGO_SHA256
            );

            if ($ok !== 1) {
                return back()->with('error', 'Firma inválida; el documento no coincide.');
            }
        }

        /** 3 · Todo correcto → descarga */
        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    /* =======================================================
     * Helper: comprueba que el archivo físico coincide
     *         con el hash almacenado en BD                   */
    private function checkIntegrity(Document $doc): bool
    {
        $path = storage_path('app/public/' . $doc->file_path);

        return file_exists($path) &&
               hash_file('sha256', $path) === $doc->file_hash;
    }
}

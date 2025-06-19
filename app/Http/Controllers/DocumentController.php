<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Document::where('user_id', Auth::id());

        if ($request->has('search') && $request->search !== '') {
            $query->where('original_name', 'like', '%' . $request->search . '%');
        }

        $documents = $query->latest()->get();

        return view('documents.index', compact('documents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('documents.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'document' => 'required|file|mimes:pdf|max:10240', // máximo 10 MB
        ]);

        $file = $request->file('document');
        $originalName = $file->getClientOriginalName();
        $content = file_get_contents($file->getRealPath());
        $hash = hash('sha256', $content);

        // Guarda el archivo en storage/app/public/documents
        $path = $file->store('documents', 'public');

        // Crea el registro en la base de datos
        Document::create([
            'original_name' => $originalName,
            'file_path' => $path,
            'file_hash' => $hash,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('documents.index')->with('success', 'Documento subido correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
         // Verifica que el documento pertenezca al usuario actual
        if ($document->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        // Elimina el archivo físico si existe
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Elimina el registro de la base de datos
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Documento eliminado correctamente.');
    }

    public function download(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->route('documents.index')
                            ->with('error', 'El archivo ya no existe en el sistema.');
        }

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

}

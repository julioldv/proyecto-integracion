<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\{
    ProfileController,
    DocumentController,
    KeyPairController,
    SignatureController
};

/* ───────────────── Página pública ───────────────── */
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('documents.index')
        : view('welcome');
});

/* ─────────────── Dashboard (opcional) ───────────── */
Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/* ──────────────── Rutas protegidas ──────────────── */
Route::middleware(['auth', 'verified'])->group(function () {

    /* -------- Perfil -------- */
    Route::get   ('/profile',  [ProfileController::class, 'edit'   ])->name('profile.edit');
    Route::patch ('/profile',  [ProfileController::class, 'update' ])->name('profile.update');
    Route::delete('/profile',  [ProfileController::class, 'destroy'])->name('profile.destroy');


    /* ==========  DOCUMENTOS  ========== */

    /* CRUD genérico  (crea, index, store, edit, update, destroy)         */
    /* lo declaramos primero para que /documents/create funcione           */
    Route::resource('documents', DocumentController::class)
        ->except('show');                   // la vista show va aparte

    /* Ficha‑detalle  (/documents/{id})  — la ponemos DESPUÉS del resource */
    Route::get('/documents/{document}', [DocumentController::class, 'show'])
        ->whereNumber('document')          // evita que “create”, “download”, etc. coincidan
        ->name('documents.show');

    /* Acciones extra */
    Route::get ('/documents/{document}/download', [DocumentController::class, 'download'])
        ->name('documents.download');
    Route::post('/documents/{document}/sign',     [SignatureController::class, 'store'])
        ->name('documents.sign');


    /* ============  LLAVES  ============ */
    Route::resource('keys', KeyPairController::class)
        ->only(['index', 'store', 'destroy']);
});


require __DIR__.'/auth.php';

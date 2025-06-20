<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KeyPairController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SignatureController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('documents', DocumentController::class);
    Route::get   ('/documents/{document}/download', [DocumentController::class,'download'])->name('documents.download');
    Route::post  ('/documents/{document}/sign', [SignatureController::class,'store'])->name('documents.sign');
    Route::resource('keys', KeyPairController::class)->only(['index','store','destroy']);

});

require __DIR__.'/auth.php';

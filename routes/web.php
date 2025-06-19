<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\DB;

Route::get('/dbcheck', function () {
    return DB::connection()->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);
});


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
    Route::resource('documents', App\Http\Controllers\DocumentController::class);
    Route::get('/documents/{document}/download', [App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');


});

require __DIR__.'/auth.php';

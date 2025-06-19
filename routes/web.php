<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KeyPairController;
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
    //Route::resource('keys', KeyPairController::class)->only(['index','create','store','destroy']);
    Route::resource('keys', KeyPairController::class)->only(['index','store','destroy']);

});


// Route::get('/openssl-test', function () {
//     $config = [
//         "private_key_bits" => 2048,
//         "private_key_type" => OPENSSL_KEYTYPE_RSA,
//     ];

//     $res = openssl_pkey_new($config);

//     if ($res === false) {
//         return '❌ Laravel no puede generar llaves con OpenSSL';
//     }

//     $privKey = '';
//     openssl_pkey_export($res, $privKey);
//     $keyDetails = openssl_pkey_get_details($res);

//     return '✅ OpenSSL funciona correctamente en Laravel.<br><br>Llave pública:<br><pre>' . $keyDetails['key'] . '</pre>';
// });

require __DIR__.'/auth.php';

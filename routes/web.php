<?php

use App\Models\Report;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::redirect('/', '/admin');

/*
 * // Pagina di benvenuto (puoi personalizzarla in futuro)
 * Route::get('/welcome', function () {
 *     return view('welcome');
 * })->name('welcome');
 *
 * ment-socialite.login', ['provider' => $provider]);
 *  })->name('login.provider');
 *
 * // Pagina di logout
 * Route::post('/logout', function () {
 *     auth()->logout();
 *     return redirect()->route('welcome');
 * })->name('logout')->middleware('auth');
 */

// Pagina di login tramite socialite
Route::get('/login/{provider}', function ($provider) {
    return redirect()->route('filament-socialite.login', ['provider' => $provider]);
})->name('login.provider');

// Pagina di registrazione tramite socialite
Route::get('/register/{provider}', function ($provider) {
    return redirect()->route('filament-socialite.register', ['provider' => $provider]);
})->name('register.provider')->middleware('guest');

// Rotta per servire i file XML
Route::get('xml/{piva}', function ($piva) {
    $report = Report::where('piva', $piva)->first();

    if (!$report) {
        abort(404, 'Report non trovato');
    }

    $media = $report->getFirstMedia('xml_files');

    if (!$media) {
        abort(404, 'File XML non trovato');
    }

    $filePath = $media->getPath();

    if (!file_exists($filePath)) {
        abort(404, 'File fisico non trovato');
    }

    return response()->file($filePath, [
        'Content-Type' => 'application/xml',
        'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
        'Cache-Control' => 'public, max-age=3600',  // Cache per 1 ora
    ]);
})->name('xml.download');
// Rotta alternativa per l'accesso diretto tramite ID media
Route::get('storage/media/{id}/{filename}', function ($id, $filename) {
    $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($id);

    if (!$media) {
        abort(404, 'Media non trovato');
    }

    $filePath = $media->getPath();

    if (!file_exists($filePath)) {
        abort(404, 'File non trovato');
    }

    return response()->file($filePath, [
        'Content-Type' => $media->mime_type,
        'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
    ]);
})->name('media.download');

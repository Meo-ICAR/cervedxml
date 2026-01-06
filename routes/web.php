<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

/*
 * // Pagina di benvenuto (puoi personalizzarla in futuro)
 * Route::get('/welcome', function () {
 *     return view('welcome');
 * })->name('welcome');
 *
ment-socialite.login', ['provider' => $provider]);
 })->name('login.provider');
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

<?php

use App\Enums\StatutRecu;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecuController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    $totalRecus = $user->recus()->count();
    $totalDepenses = $user->recus()->withCount('depenses')->get()->sum('depenses_count');
    $pendingRecus = $user->recus()->where('statut', StatutRecu::EnAttente)->count();
    $processedRecus = $user->recus()->where('statut', StatutRecu::Traite)->count();

    $recentRecus = $user->recus()
        ->withCount('depenses')
        ->latest()
        ->take(3)
        ->get();

    return view('dashboard', compact(
        'totalRecus',
        'totalDepenses',
        'pendingRecus',
        'processedRecus',
        'recentRecus',
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('recus', RecuController::class)->except(['edit', 'update']);
    Route::get('/depenses', [DepenseController::class, 'index'])->name('depenses.index');
});

require __DIR__.'/auth.php';

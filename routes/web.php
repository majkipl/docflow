<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/', [DocumentController::class, 'index'])->name('dashboard');

    Route::resource('documents', DocumentController::class)->only(['create', 'store', 'show']);

    // Akcje Workflow z wpiętym middlewarem RBAC (RBAC z Fazy 2)
    Route::post('/documents/{document}/submit', [DocumentController::class, 'submit'])->name('documents.submit');
    Route::post('/documents/{document}/approve', [DocumentController::class, 'approve'])->name('documents.approve')->middleware('role:manager,admin');
    Route::post('/documents/{document}/reject', [DocumentController::class, 'reject'])->name('documents.reject')->middleware('role:manager,admin');
});

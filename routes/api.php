<?php

use App\Http\Controllers\Api\DocumentIngestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/documents', [DocumentIngestionController::class, 'store']);
});

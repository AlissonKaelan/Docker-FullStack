<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\KanbanController;

// --- ÁREA PÚBLICA ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rotas principais (Públicas para este teste)
Route::get('/kanban', [KanbanController::class, 'index']); // O Frontend chama essa!
Route::post('/cards', [KanbanController::class, 'storeCard']);
Route::put('/cards/{id}', [KanbanController::class, 'updateCard']);

// --- ÁREA RESTRITA (Precisa de Token) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Rota para verificar quem sou eu (útil para o frontend)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Agora, só quem tem token pode mexer nos Boards
    Route::apiResource('boards', BoardController::class);
});


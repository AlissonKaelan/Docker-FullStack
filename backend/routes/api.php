<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\AuthController;

// --- ROTAS PÚBLICAS (Aberta para todos) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/ping', function () {
    return ['status' => 'API is running'];
});

// --- ROTAS PROTEGIDAS (Precisa estar logado) ---
// O middleware 'auth:sanctum' é o segurança que checa o token
Route::middleware('auth:sanctum')->group(function () {
    
    // Rota para o frontend saber quem está logado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // KANBAN
    Route::get('/kanban', [KanbanController::class, 'index']);
    Route::post('/cards', [KanbanController::class, 'storeCard']);
    Route::put('/cards/{id}', [KanbanController::class, 'updateCard']);

});
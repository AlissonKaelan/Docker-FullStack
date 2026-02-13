<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;

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
    
    // Novas Rotas (Colunas e Subtarefas)
    Route::post('/columns', [KanbanController::class, 'storeColumn']); // Criar coluna "Provas"
    Route::delete('/columns/{id}', [KanbanController::class, 'deleteColumn']); 

    Route::delete('/cards/{id}', [KanbanController::class, 'deleteCard']);
    
    Route::post('/subtasks', [KanbanController::class, 'storeSubtask']);
    Route::put('/subtasks/{id}', [KanbanController::class, 'updateSubtask']);
    Route::delete('/subtasks/{id}', [KanbanController::class, 'deleteSubtask']);

    // Rotas de Finanças
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/balance', [TransactionController::class, 'balance']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::put('/transactions/{id}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);

    // Rotas de Tarefas Diárias
    Route::apiResource('daily', \App\Http\Controllers\DailyTaskController::class);
    Route::post('/daily/reset', [\App\Http\Controllers\DailyTaskController::class, 'resetDay']);

    Route::apiResource('categories', \App\Http\Controllers\CategoryController::class);
});
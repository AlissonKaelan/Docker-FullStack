<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KanbanController;

// Rota de teste simples
Route::get('/ping', function () {
    return ['status' => 'API is running'];
});

// Rotas do Kanban
Route::get('/kanban', [KanbanController::class, 'index']); // Listar tudo
Route::post('/cards', [KanbanController::class, 'storeCard']); // Criar cartão
Route::put('/cards/{id}', [KanbanController::class, 'updateCard']); // Mover cartão
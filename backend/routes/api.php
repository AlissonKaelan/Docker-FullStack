<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceMemberController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DailyTaskController;
use App\Http\Controllers\VaultItemController;

// MIDDLEWARE DE SEGURANÇA DO PROJETO
use App\Http\Middleware\CheckWorkspaceAccess;


// ==========================================
// ROTAS PÚBLICAS (Visitantes)
// ==========================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/ping', fn() => ['status' => 'API is running']);


// ==========================================
// ROTAS PROTEGIDAS (Usuários Logados)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // ------------------------------------------
    // CONTA E AUTENTICAÇÃO (Escopo Global)
    // ------------------------------------------
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::put('/user', [UserController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // ------------------------------------------
    // WORKSPACES (Gestão de Projetos)
    // ------------------------------------------
    Route::get('/workspaces', [WorkspaceController::class, 'index']);
    Route::post('/workspaces', [WorkspaceController::class, 'store']);
    Route::put('/workspaces/{id}', [WorkspaceController::class, 'update']);
    
    Route::get('/workspaces/{workspace}/members', [WorkspaceMemberController::class, 'index']);
    Route::post('/workspaces/{workspace}/members', [WorkspaceMemberController::class, 'store']);
    Route::delete('/workspaces/{workspace}/members/{userId}', [WorkspaceMemberController::class, 'destroy']);


    // ==========================================
    // MÓDULOS DO PROJETO (Requerem o 'Workspace-Id')
    // ==========================================
    Route::middleware([CheckWorkspaceAccess::class])->group(function () {
        
        // KANBAN E COLUNAS
        Route::get('/kanban', [KanbanController::class, 'index']);
        Route::post('/columns', [KanbanController::class, 'storeColumn']);
        Route::delete('/columns/{id}', [KanbanController::class, 'deleteColumn']);

        // CARDS (Tarefas)
        // OBS: Removi o apiResource('cards') pois você usa métodos personalizados no KanbanController
        Route::post('/cards', [KanbanController::class, 'storeCard']);
        Route::put('/cards/{id}', [KanbanController::class, 'updateCard']);
        Route::delete('/cards/{id}', [KanbanController::class, 'deleteCard']);
        Route::get('/cards/{id}/transactions', [CardController::class, 'transactions']); // Custo vinculado
        
        // SUBTAREFAS DO KANBAN
        Route::post('/subtasks', [KanbanController::class, 'storeSubtask']);
        Route::put('/subtasks/{id}', [KanbanController::class, 'updateSubtask']);
        Route::delete('/subtasks/{id}', [KanbanController::class, 'deleteSubtask']);

        // FINANCEIRO (Transações e Categorias)
        Route::get('/balance', [TransactionController::class, 'balance']);
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::put('/transactions/{id}', [TransactionController::class, 'update']);
        Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);
        Route::apiResource('categories', CategoryController::class)->except(['create', 'edit', 'show']);

        // ATIVIDADES DIÁRIAS (Daily)
        Route::post('/daily/reset', [DailyTaskController::class, 'resetDay']);
        Route::apiResource('daily', DailyTaskController::class)->except(['create', 'edit', 'show']);

        // COFRE (Vault)
        Route::get('/vault', [VaultItemController::class, 'index']);
        Route::post('/vault', [VaultItemController::class, 'store']);
    });
});
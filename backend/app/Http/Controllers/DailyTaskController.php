<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyTaskController extends Controller
{
    public function index()
    {
        // Ordena: Recorrentes primeiro, depois Pendentes, depois Criadas recentemente
        return DailyTask::where('user_id', Auth::id())
            ->orderBy('is_completed', 'asc')
            ->orderBy('is_recurring', 'desc') 
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);

        $task = DailyTask::create([
            'title' => $request->title,
            'user_id' => Auth::id(),
            'is_recurring' => $request->boolean('is_recurring', false)
        ]);

        return response()->json($task, 201);
    }

    public function update(Request $request, $id)
    {
        $task = DailyTask::where('user_id', Auth::id())->findOrFail($id);
        $task->update($request->all());
        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = DailyTask::where('user_id', Auth::id())->findOrFail($id);
        $task->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // --- NOVA ROTA: INICIAR NOVO DIA ---
    public function resetDay()
    {
        $userId = Auth::id();

        // 1. Apaga tarefas comuns que foram concluídas (limpeza)
        DailyTask::where('user_id', $userId)
            ->where('is_completed', true)
            ->where('is_recurring', false)
            ->delete();

        // 2. Reseta (desmarca) as tarefas recorrentes para serem feitas de novo
        DailyTask::where('user_id', $userId)
            ->where('is_recurring', true)
            ->update(['is_completed' => false]);

        return response()->json(['message' => 'Dia resetado com sucesso']);
    }
}
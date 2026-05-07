<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyTaskController extends Controller
{
    public function index(Request $request)
    {
        return DailyTask::where('workspace_id', $request->workspace_id)
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
            'workspace_id' => $request->workspace_id,
            'user_id' => Auth::id(),
            'is_recurring' => $request->boolean('is_recurring', false)
        ]);

        return response()->json($task, 201);
    }

    public function update(Request $request, $id)
    {
        $task = DailyTask::where('workspace_id', $request->workspace_id)->findOrFail($id);
        $task->update($request->all());
        return response()->json($task);
    }

    public function destroy(Request $request, $id)
    {
        $task = DailyTask::where('workspace_id', $request->workspace_id)->findOrFail($id);
        $task->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function resetDay(Request $request)
    {
        $workspaceId = $request->workspace_id;

        DailyTask::where('workspace_id', $workspaceId)
            ->where('is_completed', true)
            ->where('is_recurring', false)
            ->delete();

        DailyTask::where('workspace_id', $workspaceId)
            ->where('is_recurring', true)
            ->update(['is_completed' => false]);

        return response()->json(['message' => 'Dia resetado com sucesso']);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{
    /**
     * Lista todos os workspaces que o usuário logado faz parte.
     */
    public function index(Request $request)
    {
        $workspaces = $request->user()->workspaces()->get();
        return response()->json($workspaces);
    }

    /**
     * Cria um novo workspace e vincula o criador como 'admin'.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // 1. Cria o Workspace
        $workspace = Workspace::create([
            'name' => $request->name,
        ]);

        // 2. Vincula o usuário logado como 'admin' na tabela pivot
        $workspace->users()->attach(Auth::id(), ['role' => 'admin']);

        return response()->json([
            'message' => 'Workspace criado com sucesso!',
            'workspace' => $workspace
        ], 201);
    }
}
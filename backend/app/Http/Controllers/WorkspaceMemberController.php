<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;

class WorkspaceMemberController extends Controller
{
    // 1. LISTAR MEMBROS DO PROJETO
    public function index(Workspace $workspace)
    {
        // Retorna todos os usuários deste projeto, incluindo o 'role' (cargo) da tabela pivot
        return response()->json($workspace->users);
    }

    // 2. ADICIONAR NOVO MEMBRO (Já tínhamos)
    public function store(Request $request, Workspace $workspace)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:admin,editor,viewer',
        ]);

        // Impede que um não-admin adicione pessoas (Segurança extra)
        $requester = $workspace->users()->where('users.id', $request->user()->id)->first();
        if (!$requester || $requester->pivot->role !== 'admin') {
            return response()->json(['message' => 'Apenas admins podem convidar membros.'], 403);
        }

        $user = User::where('email', $request->email)->first();

        if ($workspace->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Este usuário já é membro deste projeto.'], 409);
        }

        $workspace->users()->attach($user->id, ['role' => $request->role]);

        return response()->json(['message' => 'Membro adicionado com sucesso!'], 201);
    }

    // 3. REMOVER MEMBRO (Com hierarquia)
    public function destroy(Request $request, Workspace $workspace, $userId)
    {
        // Pega quem está tentando excluir
        $requester = $workspace->users()->where('users.id', $request->user()->id)->first();
        
        // Se não for admin, bloqueia!
        if (!$requester || $requester->pivot->role !== 'admin') {
            return response()->json(['message' => 'Apenas administradores podem remover membros.'], 403);
        }

        // Evita que o admin remova a si mesmo acidentalmente por aqui
        if ($request->user()->id == $userId) {
            return response()->json(['message' => 'Você não pode remover a si mesmo por aqui.'], 400);
        }

        // Corta a relação do usuário com o projeto
        $workspace->users()->detach($userId);

        return response()->json(['message' => 'Membro removido do projeto.']);
    }
}
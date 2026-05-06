<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;

class WorkspaceMemberController extends Controller
{
    // O Laravel é inteligente: como usamos {workspace} na rota, 
    // ele já busca o projeto no banco de dados e injeta aqui automaticamente!
    public function store(Request $request, Workspace $workspace)
    {
        // 1. Valida os dados de entrada
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:admin,editor,viewer',
        ]);

        // 2. Busca o usuário pelo email
        $user = User::where('email', $request->email)->first();

        // 3. A MÁGICA AQUI: Verifica se o usuário já está no workspace
        if ($workspace->users()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'Este usuário já é membro deste projeto.'
            ], 409); // 409 = Conflict
        }

        // 4. Adiciona o usuário na tabela pivot com o cargo
        $workspace->users()->attach($user->id, ['role' => $request->role]);

        return response()->json([
            'message' => 'Membro adicionado com sucesso!',
            'user' => $user
        ], 201);
    }
}
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
        // 1. Validação de Segurança (Nunca confie no Front-end!)
        $request->validate([
            'email' => 'required|email|exists:users,email', // Garante que o e-mail existe no nosso sistema
            'role' => 'required|in:editor,viewer' // Garante que não inventem permissões malucas
        ]);

        // 2. Buscamos o usuário no banco usando o e-mail fornecido
        $userToInvite = User::where('email', $request->email)->first();

        // 3. Salvamos a relação na tabela pivot (igual fizemos no Tinker!)
        $workspace->users()->attach($userToInvite->id, [
            'role' => $request->role
        ]);

        // 4. Retornamos status 200 OK
        return response()->json([
            'message' => 'Membro adicionado com sucesso!'
        ], 200);
    }
}
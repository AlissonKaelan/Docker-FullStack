<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckWorkspaceAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Pega o ID do projeto que o Vue enviou no Cabeçalho
        $workspaceId = $request->header('Workspace-Id');

        if (!$workspaceId) {
            return response()->json(['message' => 'O ID do Workspace não foi fornecido.'], 400);
        }

        // 2. Confere se o usuário tem permissão para acessar esse Workspace
        $hasAccess = $request->user()->workspaces()->where('workspaces.id', $workspaceId)->exists();

        if (!$hasAccess) {
            return response()->json(['message' => 'Acesso negado a este projeto.'], 403);
        }

        // 3. A Mágica: Injeta o workspace_id na requisição para os Controllers usarem
        $request->merge(['workspace_id' => $workspaceId]);

        return $next($request);
    }
}
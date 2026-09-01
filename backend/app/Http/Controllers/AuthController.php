<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 1. REGISTRAR NOVO USUÁRIO
    public function register(Request $request)
    {
        // Validação com mensagens 100% em português e amigáveis
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed' 
        ], [
            'name.required' => 'Por favor, informe o seu nome completo.',
            'email.required' => 'O campo de e-mail é obrigatório.',
            'email.email' => 'Por favor, digite um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado em nosso sistema. Tente fazer login.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A sua senha deve ter pelo menos 8 caracteres para sua segurança.',
            'password.confirmed' => 'As senhas digitadas não coincidem. Verifique e tente novamente.'
        ]);

        // Criar usuário no banco
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 1. CRIA O WORKSPACE PADRÃO PARA O NOVO USUÁRIO
        $workspace = Workspace::create([
            'name' => 'Meu Workspace Pessoal'
        ]);

        // 2. VINCULA ELE COMO ADMIN
        $workspace->users()->attach($user->id, ['role' => 'admin']);

        // 3. CRIA AS COLUNAS DENTRO DO WORKSPACE E NÃO MAIS NO USUÁRIO
        $workspace->columns()->createMany([
            ['title' => 'A Fazer', 'slug' => 'todo', 'order' => 1, 'user_id' => $user->id],
            ['title' => 'Em Progresso', 'slug' => 'doing', 'order' => 2, 'user_id' => $user->id],
            ['title' => 'Concluído', 'slug' => 'done', 'order' => 3, 'user_id' => $user->id],
        ]);

        // Gerar o Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuário cadastrado com sucesso!',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 1. Busca o usuário pelo e-mail
        $user = User::where('email', $request->email)->first();

        // 2. Faz a verificação manual do Hash da senha (100% Stateless, sem Sessão)
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        // 3. Gera o Token limpo do Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado!',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    // 3. LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout realizado com sucesso']);
    }
}
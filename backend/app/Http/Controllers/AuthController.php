<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            'password' => 'required|string|min:8|confirmed' // 'confirmed' checa o password_confirmation automaticamente
        ], [
            // Aqui você dita exatamente o que o usuário vai ler
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

        // Cria automaticamente as 3 colunas para este novo usuário
        $user->columns()->createMany([
            ['title' => 'A Fazer', 'slug' => 'todo', 'order' => 1],
            ['title' => 'Em Progresso', 'slug' => 'doing', 'order' => 2],
            ['title' => 'Concluído', 'slug' => 'done', 'order' => 3],
        ]);

        // Gerar o Token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Retornar usuário e token
        return response()->json([
            'message' => 'Usuário cadastrado com sucesso!',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    //Login
    public function login(Request $request)
    {
        // 1. Valida se o usuário preencheu os campos antes de tentar ir no banco
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Tenta fazer o login
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Dispara um erro 422 simulando que o erro foi no campo "email"
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Apaga tokens antigos e cria um novo (segurança extra)
        // $user->tokens()->delete(); 
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
        // Revoga o token atual
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout realizado com sucesso']);
    }
}
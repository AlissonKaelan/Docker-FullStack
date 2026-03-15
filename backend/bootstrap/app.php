<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        // 1. Padroniza Rota Não Encontrada (404) ou Recurso (Model) não encontrado
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'O recurso que você está procurando não foi encontrado.'
                ], 404);
            }
        });

        // 2. Padroniza Erro de Autenticação (Acesso Negado)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Sua sessão expirou ou você não tem permissão para acessar.'
                ], 401);
            }
        });

        // 3. Padroniza Erro Interno do Servidor (500) - Oculta detalhes do banco
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') && !config('app.debug')) {
                return response()->json([
                    'message' => 'Ops! Ocorreu um erro interno no servidor. Nossa equipe já foi notificada.'
                ], 500);
            }
        });
    })->create();
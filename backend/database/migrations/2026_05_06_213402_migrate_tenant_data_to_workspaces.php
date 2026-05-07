<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. As tabelas que vão receber a nova arquitetura
        $tables = ['columns', 'categories', 'transactions', 'daily_tasks'];
        
        // Fase 1: Adiciona a coluna workspace_id (permitindo nulo temporariamente)
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->foreignId('workspace_id')
                            ->nullable()
                            ->constrained('workspaces')
                            ->onDelete('cascade');
            });
        }

        // Fase 2: O Resgate (Migração de Dados)
        // Busca todos os usuários que já existem no banco
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            // Cria um Workspace padrão para salvar os dados antigos do usuário
            $workspaceId = DB::table('workspaces')->insertGetId([
                'name' => 'Meu Workspace Pessoal',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Dá permissão de "admin" para o usuário neste novo workspace
            DB::table('workspace_user')->insert([
                'workspace_id' => $workspaceId,
                'user_id' => $user->id,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Fase 3: Realocação
            // Atualiza todos os registros antigos para apontar para o novo Workspace
            foreach ($tables as $table) {
                DB::table($table)
                    ->where('user_id', $user->id)
                    ->update(['workspace_id' => $workspaceId]);
            }
        }
    }

    public function down()
    {
        // Se precisarmos dar rollback, apagamos a chave estrangeira e a coluna
        $tables = ['columns', 'categories', 'transactions', 'daily_tasks'];
        
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->dropForeign(['workspace_id']);
                $tableBlueprint->dropColumn('workspace_id');
            });
        }
    }
};
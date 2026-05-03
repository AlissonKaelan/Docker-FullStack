<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_owner_can_add_a_member_by_email()
    {
        // 1. ARRANGE (Preparação)
        // Criamos o usuário que é o dono e o projeto
        $owner = User::factory()->create();
        $workspace = Workspace::factory()->create();
        
        // Colocamos o dono dentro do projeto com a permissão correta
        $workspace->users()->attach($owner->id, ['role' => 'owner']);

        // Criamos o usuário que será convidado (simulando que ele já tem conta no sistema)
        $newMember = User::factory()->create(['email' => 'amigo@teste.com']);

        // 2. ACT (Ação)
        // Simulamos o dono logado enviando o formulário lá do Vue.js
        $response = $this->actingAs($owner)->postJson("/api/workspaces/{$workspace->id}/members", [
            'email' => 'amigo@teste.com',
            'role' => 'editor'
        ]);

        // 3. ASSERT (Verificações)
        // Esperamos que a API diga "OK"
        $response->assertStatus(200);

        // Verificamos se o amigo realmente foi parar na tabela pivot do banco de dados!
        $this->assertDatabaseHas('workspace_user', [
            'workspace_id' => $workspace->id,
            'user_id' => $newMember->id,
            'role' => 'editor'
        ]);
    }
}
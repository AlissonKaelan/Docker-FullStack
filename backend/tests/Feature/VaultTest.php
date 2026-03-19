<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VaultItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class VaultTest extends TestCase
{
    use RefreshDatabase; // Limpa o banco a cada teste

    
    public function a_user_can_create_a_vault_item()
    {
        // 1. Criamos um usuário de teste
        $user = User::factory()->create();

        // 2. Simulamos que ele está logado
        $this->actingAs($user);

        // 3. Dados que queremos salvar no cofre
        $payload = [
            'title' => 'Minha Senha do Wi-Fi',
            'content' => '12345678',
            'type' => 'note' // pode ser 'note' ou 'link'
        ];

        // 4. Fazemos uma requisição POST para a API
        $response = $this->postJson('/api/vault', $payload);

        // 5. Verificamos se deu certo (Status 201 - Created)
        $response->assertStatus(201);

        // 6. Verificamos se o dado existe no banco de dados
        $this->assertDatabaseHas('vault_items', [
            'user_id' => $user->id,
            'title' => 'Minha Senha do Wi-Fi'
        ]);
    }
}
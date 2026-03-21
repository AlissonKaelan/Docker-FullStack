<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VaultItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class VaultTest extends TestCase
{
    use RefreshDatabase; // Apenas isso fica aqui!

    #[Test]
    public function a_user_can_create_a_vault_item()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'title' => 'Minha Senha do Wi-Fi',
            'content' => '12345678',
            'type' => 'note' 
        ];

        $response = $this->postJson('/api/vault', $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('vault_items', [
            'user_id' => $user->id,
            'title' => 'Minha Senha do Wi-Fi'
        ]);
    }

    #[Test]
    public function a_user_can_list_their_own_vault_items()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        VaultItem::factory()->count(3)->create([
            'user_id' => $user->id
        ]);

        VaultItem::factory()->count(2)->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->actingAs($user)->getJson('/api/vault');
        $response->assertStatus(200);
        $response->assertJsonCount(3); 
    }
}
<?php

use App\Models\User;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a transaction with category', function () {
    $user = User::factory()->create();
    
    // Cria a categoria vinculada ao usuário para evitar erro de chave estrangeira
    $category = Category::create([
        'name' => 'Mercado', 
        'color' => '#000', 
        'type' => 'expense', 
        'user_id' => $user->id
    ]);

    $payload = [
        'description' => 'Compras do Mês',
        'amount' => 500.00,
        'type' => 'expense',
        'transaction_date' => '2026-02-05',
        'category_id' => $category->id,
        'installments' => 1
    ];

    // MUDANÇA: $this->actingAs(...)
    $response = $this->actingAs($user)->postJson('/api/transactions', $payload);

    $response->assertStatus(201);

    $this->assertDatabaseHas('transactions', [
        'description' => 'Compras do Mês',
        'amount' => 500.00,
        'category_id' => $category->id,
        'user_id' => $user->id
    ]);
});

it('creates multiple records for installments', function () {
    $user = User::factory()->create();

    $payload = [
        'description' => 'Notebook Gamer',
        'amount' => 3000.00,
        'type' => 'expense',
        'transaction_date' => '2026-02-05',
        'installments' => 3
    ];

    $response = $this->actingAs($user)->postJson('/api/transactions', $payload);

    $response->assertStatus(201);

    expect(Transaction::where('user_id', $user->id)->count())->toBe(3);

    $this->assertDatabaseHas('transactions', [
        'description' => 'Notebook Gamer (1/3)',
        'amount' => 1000.00,
        'transaction_date' => '2026-02-05 00:00:00'
    ]);

    // Teste ajustado para a lógica "addMonthsNoOverflow" (Fev tem 28 dias)
    // Se a data base é dia 05, +2 meses deve ser dia 05 de Abril.
    $this->assertDatabaseHas('transactions', [
        'description' => 'Notebook Gamer (3/3)',
        'amount' => 1000.00,
        'transaction_date' => '2026-04-05 00:00:00'
    ]);
});
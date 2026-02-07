<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a category', function () {
    $user = User::factory()->create();

    // MUDANÇA: $this->actingAs(...) em vez de actingAs(...)
    $response = $this->actingAs($user)->postJson('/api/categories', [
        'name' => 'Alimentação',
        'color' => '#FF0000',
        'type' => 'expense'
    ]);

    $response->assertStatus(201)
             ->assertJson(['name' => 'Alimentação']);

    $this->assertDatabaseHas('categories', [ // MUDANÇA: $this->assert...
        'name' => 'Alimentação',
        'user_id' => $user->id
    ]);
});

it('cannot create a category without a name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/categories', [
        'color' => '#FF0000',
        'type' => 'expense'
    ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['name']);
});
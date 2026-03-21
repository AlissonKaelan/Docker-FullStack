<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VaultItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), 
            
            // Ajustado para bater exatamente com as colunas da sua tabela!
            'title' => fake()->sentence(3), // Gera um título falso de 3 palavras
            'content' => fake()->password(12), // Gera um conteúdo/senha falso de 12 caracteres
            'type' => fake()->randomElement(['note', 'link', 'password']), // Escolhe um tipo aleatório
        ];
    }
}
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WorkspaceFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Usa o Faker para gerar um nome de empresa/projeto aleatório
            'name' => fake()->company() . ' Workspace',
        ];
    }
}
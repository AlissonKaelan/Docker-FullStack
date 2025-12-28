<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Column;
use App\Models\Card;

class KanbanSeeder extends Seeder
{
    public function run(): void
    {
        // Limpa a tabela antes de criar (opcional, mas bom para testes)
        // Column::truncate(); 
        // Card::truncate();

        // 1. Criar as Colunas
        $todo = Column::create(['title' => 'A Fazer', 'slug' => 'todo', 'order_index' => 1]);
        $doing = Column::create(['title' => 'Em Progresso', 'slug' => 'doing', 'order_index' => 2]);
        $done = Column::create(['title' => 'Concluído', 'slug' => 'done', 'order_index' => 3]);

        // 2. Criar alguns cartões de exemplo
        Card::create([
            'title' => 'Estudar Docker',
            'description' => 'Aprender a criar containers e volumes',
            'column_id' => $todo->id,
            'order_index' => 1
        ]);

        Card::create([
            'title' => 'Configurar Vue.js',
            'description' => 'Instalar Vite e Axios',
            'column_id' => $todo->id,
            'order_index' => 2
        ]);
    }
}
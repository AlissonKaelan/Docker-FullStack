<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Adicionar porcentagem aos Cards
        Schema::table('cards', function (Blueprint $table) {
            $table->integer('percentage')->default(0)->after('description');
        });

        // 2. Criar tabela de Subtarefas
        Schema::create('subtasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('cards')->onDelete('cascade'); // Se deletar o card, as subtasks somem
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtasks');
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn('percentage');
        });
    }
};
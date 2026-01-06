<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // 'string' é limitado a 255 caracteres, perfeito para títulos como "Conta de Luz".
            $table->string('description'); 

            // O 'enum' protege o banco: só aceita 'income' (entrada) ou 'expense' (saída).
            $table->enum('type', ['income', 'expense']);

            
            // 8 digitos pode ser pouco se alguém ficar rico! :)
            $table->decimal('amount', 10, 2);

            
            // constrained() cria o vínculo com a tabela 'users'.
            // onDelete('cascade') apaga as transações se o usuário for excluído.
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->date('transaction_date');

            
            // Isso cria as colunas 'created_at' e 'updated_at'
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        // 6. SEGURANÇA: Se rodar o comando 'rollback', a tabela é apagada.
        Schema::dropIfExists('transactions');
    }
};
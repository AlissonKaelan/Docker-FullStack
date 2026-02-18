<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Cria a coluna card_id, que pode ser nula (gastos gerais)
            // e se o card for deletado, o gasto continua existindo (set null)
            $table->foreignId('card_id')->nullable()->constrained('cards')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Remove a chave estrangeira e a coluna
            $table->dropForeign(['card_id']);
            $table->dropColumn('card_id');
        });
    }
};

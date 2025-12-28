<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('columns', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: "A Fazer", "Em Progresso"
            $table->foreignId('board_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0); // Para você poder reordenar as colunas (1, 2, 3...)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('columns');
    }
};

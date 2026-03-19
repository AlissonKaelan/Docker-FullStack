<?php

use Illuminate\Database\Schema\Blueprint; // Certifique-se de que esta linha está no topo
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Aqui estava o erro: troque (Table $table) por (Blueprint $table)
        Schema::create('vault_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->string('type')->default('note');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_items');
    }
};

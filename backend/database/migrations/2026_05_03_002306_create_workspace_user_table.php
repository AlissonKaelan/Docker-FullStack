<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_user', function (Blueprint $table) {
            $table->id();
            
            // Ligações com deleção em cascata (Se apagar o workspace, apaga o vínculo)
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // O nível de acesso (Dono, Editor, Leitor)
            $table->string('role')->default('viewer'); 
            
            $table->timestamps();

            // Garante que o mesmo usuário não seja adicionado duas vezes no mesmo workspace
            $table->unique(['workspace_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_user');
    }
};
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
        // Esta migration documenta a adição de novas funções de funcionários:
        // - washer (Lavador)
        // - ironer (Passador)
        // - attendant (Atendente)
        // - driver (Motorista)
        // 
        // Estas funções são armazenadas no campo 'role' da tabela employees
        // e mapeiam para a role 'employee' na tabela users
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não há alterações de schema para reverter
    }
};

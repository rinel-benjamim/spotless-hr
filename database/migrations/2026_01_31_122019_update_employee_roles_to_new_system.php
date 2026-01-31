<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mapear roles antigos para novos
        $roleMapping = [
            'operator' => 'employee',
            'washer' => 'employee', 
            'ironer' => 'employee',
            'supervisor' => 'manager',
            'delivery_driver' => 'employee',
            'customer_service' => 'employee',
            // Manter os que já existem
            'manager' => 'manager',
            'admin' => 'admin',
            'employee' => 'employee'
        ];
        
        foreach ($roleMapping as $oldRole => $newRole) {
            DB::table('employees')
                ->where('role', $oldRole)
                ->update(['role' => $newRole]);
        }
    }

    public function down(): void
    {
        // Não é possível reverter pois perdemos informação específica
        // dos roles antigos
    }
};

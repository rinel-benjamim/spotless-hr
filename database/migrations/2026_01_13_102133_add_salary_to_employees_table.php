<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('base_salary', 10, 2)->nullable()->after('hire_date');
            $table->decimal('deduction_per_absence', 10, 2)->nullable()->after('base_salary');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['base_salary', 'deduction_per_absence']);
        });
    }
};

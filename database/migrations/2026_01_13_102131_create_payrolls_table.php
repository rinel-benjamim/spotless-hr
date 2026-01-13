<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('reference_month');
            $table->decimal('base_salary', 10, 2);
            $table->integer('total_days_worked')->default(0);
            $table->integer('absences_count')->default(0);
            $table->integer('late_count')->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('total_bonus', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'reference_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};

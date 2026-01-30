<?php

use App\ContractType;
use App\EmployeeRole;
use App\EmployeeStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code')->unique();
            $table->string('full_name');
            $table->string('role')->default(EmployeeRole::Employee->value);
            $table->string('contract_type')->default(ContractType::FullTime->value);
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default(EmployeeStatus::Active->value);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

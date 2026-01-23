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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->integer('early_exit_count')->default(0)->after('late_count');
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->decimal('late_deduction_amount', 8, 2)->default(0)->after('currency');
            $table->decimal('early_exit_deduction_amount', 8, 2)->default(0)->after('late_deduction_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('early_exit_count');
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['late_deduction_amount', 'early_exit_deduction_amount']);
        });
    }
};

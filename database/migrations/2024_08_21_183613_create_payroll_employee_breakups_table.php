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
        Schema::create('payroll_employee_breakups', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('payroll_employee_id')->index();
            $table->bigInteger('amountable_id')->index();
            $table->bigInteger('amountable_type')->index();
            $table->bigInteger('name_in_payslip')->index();
            $table->bigInteger('standard_amount')->index();
            $table->bigInteger('actual_payable_amount')->index();
            $table->bigInteger('employer_contribution_amount')->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_employee_breakups');
    }
};

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
            $table->string('amountable_type');
            $table->string('name_in_payslip');
            $table->double('standard_amount')->default(0);
            $table->double('actual_payable_amount')->default(0);
            $table->double('employer_contribution_amount')->default(0);

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

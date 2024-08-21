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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('financial_year_id')->index();
            $table->string('payroll_name');
            $table->date('from');
            $table->date('to');
            $table->double('working_days');
            $table->double('actual_days');
            $table->double('ctc');
            $table->double('basic_pay');
            $table->double('gross_pay');
            $table->double('total_earning');
            $table->double('overtime_earning');
            $table->double('reimbursement');
            $table->double('loan_disbursal');
            $table->double('gross_salary');
            $table->double('gross_deduction');
            $table->double('net_payable_amount');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};

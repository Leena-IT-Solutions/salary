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
            $table->double('working_days')->default(0);
            $table->double('actual_days')->default(0);
            $table->double('ctc')->default(0);
            $table->double('basic_pay')->default(0);
            $table->double('gross_pay')->default(0);
            $table->double('total_earning')->default(0);
            $table->double('overtime_earning')->default(0);
            $table->double('reimbursement')->default(0);
            $table->double('loan_disbursal')->default(0);
            $table->double('gross_salary')->default(0);
            $table->double('gross_deduction')->default(0);
            $table->double('net_payable_amount')->default(0);

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

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
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('employee_id')->index();
            $table->bigInteger('salary_group_id')->index();
            $table->date('effective_from');
            $table->string('note')->nullable();
            
            $table->double('ctc');
            $table->double('checking_gross_pay');
            $table->double('gross_pay');
            $table->double('basic_pay');
            $table->double('net_pay');
            $table->double('employer_contribution');
            $table->double('remaining_amount');
            $table->double('earnings_total');
            $table->double('total_gross_percentage');
            $table->double('per_hour');
            $table->double('per_minute');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salaries');
    }
};

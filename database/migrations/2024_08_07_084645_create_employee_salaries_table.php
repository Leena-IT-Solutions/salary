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
            $table->double('employer_contribution')->nullable();
            $table->double('gross')->nullable();
            $table->double('basic_pay')->nullable();
            $table->double('remaining_amount')->nullable();
            $table->double('earnings_total')->nullable();
            $table->double('total_gross_percentage')->nullable();

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

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
        Schema::create('payroll_employee_attendances', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('payroll_employee_id')->index();
            $table->double('lop');
            $table->double('payable_days');
            $table->double('ot_hours');
            $table->double('ot_amount');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_employee_attendances');
    }
};

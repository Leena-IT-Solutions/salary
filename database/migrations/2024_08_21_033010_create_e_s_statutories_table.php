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
        Schema::create('e_s_statutories', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('employee_salary_id')->index();
            $table->bigInteger('salary_group_id')->index();
            $table->bigInteger('statutory_compliance_id')->index();
            $table->bigInteger('statutory_compliance_condition_id')->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_s_statutories');
    }
};

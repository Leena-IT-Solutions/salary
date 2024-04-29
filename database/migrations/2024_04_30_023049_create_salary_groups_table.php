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
        Schema::create('salary_groups', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->bigInteger('monthly_gross_salary')->default(0);
            $table->bigInteger('anual_salary')->default(0);
            $table->bigInteger('cost_to_company')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_groups');
    }
};

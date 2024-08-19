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
        Schema::create('variable_pay_approvals', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('employee_id')->index();
            $table->bigInteger('earning_id')->index();
            $table->date('app_date');
            $table->integer('amount');
            $table->string('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variable_pay_approvals');
    }
};

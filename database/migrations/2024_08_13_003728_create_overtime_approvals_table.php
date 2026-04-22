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
        Schema::create('overtime_approvals', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('employee_id')->index();
            $table->bigInteger('employee_shift_id')->index();
            $table->date('on_date');
            $table->smallInteger('hrs');
            $table->string('note')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_approvals');
    }
};

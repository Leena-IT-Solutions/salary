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
        Schema::create('leave_approvals', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('employee_id')->index();
            $table->bigInteger('leave_master_id')->index();
            $table->bigInteger('employee_shift_id')->index();
            $table->date('on_date');
            $table->string('reason')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->enum('is_halfday', ['Yes', 'No']);
            $table->enum('is_lop', ['Yes', 'No']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_approvals');
    }
};

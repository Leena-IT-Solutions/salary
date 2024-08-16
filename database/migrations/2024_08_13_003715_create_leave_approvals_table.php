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
            $table->set('status', ['Approved', 'Rejected']);
            $table->set('is_halfday', ['Yes', 'No']);
            $table->set('is_lop', ['Yes', 'No']);

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

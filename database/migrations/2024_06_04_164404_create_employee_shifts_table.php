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
        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('employee_id')->index();
            $table->bigInteger('working_shift_id')->index();
            $table->date('dt');
            $table->integer('late')->default(0);
            $table->integer('early')->default(0);
            $table->double('lop')->default(1);
            $table->enum('status', [
                'Present',
                'Working',
                'Halfday Working',
                'Halfday Leave',
                'Short Leave',
                'Leave',
                'Time Update',
                'On Duty',
                'Weekoff',
                'Holiday',
                'Absent',
            ])->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_shifts');
    }
};

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
        Schema::create('short_leaves', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('employee_id')->index();
            $table->bigInteger('employee_shift_id')->index();
            $table->date('on_date');
            $table->time('in_time')->format('H:i')->nullable();
            $table->time('out_time')->format('H:i')->nullable();
            $table->set('status', ['Approved', 'Rejected']);
            $table->set('is_lop', ['Yes', 'No']);
            $table->string('reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_leaves');
    }
};

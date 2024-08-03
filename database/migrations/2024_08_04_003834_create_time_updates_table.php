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
        Schema::create('time_updates', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('employee_id')->index();
            $table->date('on_date');
            $table->time('in_time')->format('H:i');
            $table->time('out_time')->format('H:i');
            $table->string('reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_updates');
    }
};

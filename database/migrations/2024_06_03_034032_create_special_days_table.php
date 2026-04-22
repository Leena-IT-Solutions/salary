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
        Schema::create('special_days', function (Blueprint $table) {
            $table->id();

            $table->date('special_day')->nullable();
            $table->enum('day_type', ['Weekoff', 'Holiday', 'Halfday']);
            $table->string('remark');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('special_days');
    }
};

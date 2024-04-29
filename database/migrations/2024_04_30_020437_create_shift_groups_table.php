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
        Schema::create('shift_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('in')->format('H:i');
            $table->time('out')->format('H:i');
            $table->boolean('is_next_day_out')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_groups');
    }
};

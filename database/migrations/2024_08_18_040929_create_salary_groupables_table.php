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
        Schema::create('salary_groupables', function (Blueprint $table) {
            $table->foreignId('salary_group_id');
            $table->bigInteger('salary_groupable_id')->index();
            $table->string('salary_groupable_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_groupables');
    }
};

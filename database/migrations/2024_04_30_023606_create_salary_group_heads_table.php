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
        Schema::create('salary_group_heads', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('salary_group_id')->index();
            $table->bigInteger('salary_master_id')->index();
            $table->bigInteger('monthly_amount');
            $table->bigInteger('yearly_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_group_heads');
    }
};

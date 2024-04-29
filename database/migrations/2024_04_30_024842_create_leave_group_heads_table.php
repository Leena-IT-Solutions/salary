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
        Schema::create('leave_group_heads', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('leave_group_id')->index();
            $table->bigInteger('leave_master_id')->index();
            $table->smallInteger('no_of_leaves');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_group_heads');
    }
};

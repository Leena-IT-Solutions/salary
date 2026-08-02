<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_shifts', function (Blueprint $table) {
            $table->index(['employee_id', 'dt']);
            $table->index(['dt', 'late']);
            $table->index(['dt', 'early']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_shifts', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'dt']);
            $table->dropIndex(['dt', 'late']);
            $table->dropIndex(['dt', 'early']);
        });
    }
};

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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('old_pf')->nullable()->after('pf');
            $table->string('old_uan')->nullable()->after('uan');
            $table->string('old_esic')->nullable()->after('esic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['old_pf', 'old_uan', 'old_esic']);
        });
    }
};

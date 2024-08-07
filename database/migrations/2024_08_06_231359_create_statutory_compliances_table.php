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
        Schema::create('statutory_compliances', function (Blueprint $table) {
            $table->id();

            $table->string('scheme_name');
            $table->string('abbreviation');
            $table->string('registration_number');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_part_of_salary')->default(false);
            $table->boolean('is_pro_rata')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statutory_compliances');
    }
};

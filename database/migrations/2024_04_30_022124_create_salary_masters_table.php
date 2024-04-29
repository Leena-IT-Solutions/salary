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
        Schema::create('salary_masters', function (Blueprint $table) {
            $table->id();

            $table->string('head');
            $table->set('side', ['Debit', 'Credit']);
            $table->set('rule', ['Fixed', 'Percent', 'Standard']);
            $table->double('multiplier')->default(1);
            $table->set('repeat', [1,2,3,4,5,6,7,8,9,10,11,12])->default(12);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_masters');
    }
};

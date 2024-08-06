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
        Schema::create('services_components', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('services_type_id')->index();
            $table->string('name');
            $table->string('name_in_payslip');
            $table->set('calculation', ['Flat', 'CTC', 'Basic']);
            $table->set('pay_time', ['Fixed', 'Variable']);
            $table->double('value');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_pro_rata')->default(false);
            $table->boolean('is_in_payslip')->default(false);
            $table->boolean('is_compulsory')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services_components');
    }
};

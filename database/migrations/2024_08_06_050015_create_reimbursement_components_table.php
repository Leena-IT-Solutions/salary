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
        Schema::create('reimbursement_components', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('reimbursement_type_id')->index();
            $table->string('name');
            $table->string('name_in_payslip');
            $table->double('value');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_annual')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reimbursement_components');
    }
};

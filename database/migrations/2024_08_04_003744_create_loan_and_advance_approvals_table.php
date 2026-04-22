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
        Schema::create('loan_and_advance_approvals', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('employee_id')->index();
            $table->date('application_date');
            $table->date('disbursed_date')->nullable();
            $table->date('close_date')->nullable();
            $table->integer('loan_amount');
            $table->integer('emi_amount');
            $table->double('rate_of_interest');
            $table->integer('tenure');
            $table->enum('status', ['Approved', 'Rejected']);
            $table->string('reason')->nullable();
            $table->enum('is_pause', ['Yes', 'No']);
            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_and_advance_approvals');
    }
};

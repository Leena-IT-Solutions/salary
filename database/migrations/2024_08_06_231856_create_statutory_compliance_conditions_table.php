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
        Schema::create('statutory_compliance_conditions', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('statutory_compliance_id')->index();
            $table->enum('gender', ['All', 'Male', 'Female', 'Other']);
            $table->enum('salary_type', ['Basic Pay', 'Gross Pay', 'CTC', 'None']);
            $table->enum('calculation', ['Flat', 'Percentage', 'CSV']);
            $table->double('min_salary')->nullable();
            $table->double('max_salary')->nullable();
            $table->double('restrict_salary_for_calculation')->nullable();
            $table->text('employee_contribution')->nullable();
            $table->text('max_employee_contribution')->nullable();
            $table->text('employer_contribution')->nullable();
            $table->text('max_employer_contribution')->nullable();
            $table->boolean('is_active')->default(false);
            $table->enum('state',[
                'All',
                'Andhra Pradesh',
                'Arunachal Pradesh',
                'Assam',
                'Bihar',
                'Chhattisgarh',
                'Goa',
                'Gujarat',
                'Haryana',
                'Himachal Pradesh',
                'Jharkhand',
                'Karnataka',
                'Kerala',
                'Maharashtra',
                'Madhya Pradesh',
                'Manipur',
                'Meghalaya',
                'Mizoram',
                'Nagaland',
                'Odisha',
                'Punjab',
                'Rajasthan',
                'Sikkim',
                'Tamil Nadu',
                'Tripura',
                'Telangana',
                'Uttar Pradesh',
                'Uttarakhand',
                'West Bengal',
                'Andaman & Nicobar',
                'Chandigarh',
                'Dadra & Nagar Haveli',
                'Daman & Diu',
                'Delhi',
                'Jammu & Kashmir',
                'Ladakh',
                'Lakshadweep',
                'Puducherry',
            ]);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statutory_compliance_conditions');
    }
};

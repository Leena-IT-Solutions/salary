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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('first_name', 65);
            $table->string('middle_name', 65)->nullable();
            $table->string('last_name', 65);
            $table->string('employee_code', 65)->unique()->index();
            $table->string('tagid', 65)->nullable();
            $table->string('email', 100);
            $table->string('phone', 12);
            $table->date('doj')->nullable();
            $table->date('doe')->nullable();
            $table->date('dob');
            $table->set('gender', ['Male', 'Female', 'Other']);
            $table->set('blood_group', ['O +ve', 'O -ve', 'A +ve', 'A -ve', 'B +ve', 'B -ve', 'AB +ve', 'AB -ve', 'HH', 'Other']);
            $table->set('religion', ['Hindu', 'Muslim', 'Christian', 'Sikh', 'Buddhist', 'Jain', 'Atheist', 'Other']);
            $table->string('cast', 100)->nullable();
            $table->string('subcast', 100)->nullable();
            $table->string('mothertongue', 100);
            $table->string('nationality', 100);
            $table->set('marital_status', ['Married', 'Widowed', 'Separated', 'Divorced', 'Single', 'Other']);
            $table->set('qualification', ['Primary School', 'Secondary School', 'High School', 'Undergraduate', 'Graduate', 'Diploma', 'Masters', 'Doctorate', 'Other'])->nullable();
            $table->string('degree', 100)->nullable();
            $table->string('aadhar', 16)->nullable();
            $table->string('pan', 16)->nullable();
            $table->string('pf', 100)->nullable();
            $table->string('uan', 100)->nullable();
            $table->string('esic', 100)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

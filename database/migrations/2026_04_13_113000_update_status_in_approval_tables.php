<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            // Update leave_approvals
            DB::statement("ALTER TABLE leave_approvals MODIFY COLUMN status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending'");

            // Update short_leaves
            DB::statement("ALTER TABLE short_leaves MODIFY COLUMN status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending'");

            // Update overtime_approvals
            DB::statement("ALTER TABLE overtime_approvals MODIFY COLUMN status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE leave_approvals MODIFY COLUMN status ENUM('Approved', 'Rejected')");
            DB::statement("ALTER TABLE short_leaves MODIFY COLUMN status ENUM('Approved', 'Rejected')");
            DB::statement("ALTER TABLE overtime_approvals MODIFY COLUMN status ENUM('Approved', 'Rejected')");
        }
    }
};

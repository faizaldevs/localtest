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
        Schema::create('staff_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->date('payment_date');            $table->date('period_from');
            $table->date('period_to');
            $table->decimal('base_amount', 10, 2)->default(0); // Base salary/daily wage/incentive
            $table->decimal('bonus_amount', 10, 2)->default(0); // Additional bonus
            $table->decimal('total_gross_amount', 10, 2); // Total before deductions
            $table->decimal('loan_deduction', 10, 2)->default(0); // Loan repayment deduction
            $table->decimal('discrepancy_deduction', 10, 2)->default(0); // Discrepancy deduction
            $table->decimal('other_deductions', 10, 2)->default(0); // Other deductions
            $table->decimal('total_deductions', 10, 2)->default(0); // Total deductions
            $table->decimal('net_amount_paid', 10, 2); // Final amount paid
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_payments');
    }
};

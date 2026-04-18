<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_collection_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_repayment_id')->constrained('loan_repayments')->cascadeOnDelete();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->tinyInteger('outcome')->comment('1=Reached, 2=Unreachable, 3=Promised to Pay, 4=Escalated, 5=Reminder Sent');
            $table->text('note');
            $table->date('next_action_date')->nullable();
            $table->date('promised_payment_date')->nullable();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['loan_repayment_id', 'outcome']);
            $table->index(['loan_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_collection_follow_ups');
    }
};

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
        Schema::table('members', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'members_tenant_status_idx');
            $table->index(['tenant_id', 'user_id'], 'members_tenant_user_idx');
            $table->index(['tenant_id', 'branch_id', 'status'], 'members_tenant_branch_status_idx');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'loans_tenant_status_idx');
            $table->index(['tenant_id', 'borrower_id', 'status'], 'loans_tenant_borrower_status_idx');
            $table->index(['tenant_id', 'branch_id', 'status'], 'loans_tenant_branch_status_idx');
        });

        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'deposit_requests_tenant_status_idx');
        });

        Schema::table('withdraw_requests', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'withdraw_requests_tenant_status_idx');
        });

        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->index(['tenant_id', 'status', 'repayment_date'], 'loan_repayments_tenant_status_date_idx');
            $table->index(['tenant_id', 'loan_id', 'status'], 'loan_repayments_tenant_loan_status_idx');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['tenant_id', 'recipient_id', 'status'], 'messages_tenant_recipient_status_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id', 'read_at', 'created_at'], 'notifications_owner_read_created_idx');
        });

        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->index(['tenant_id', 'name'], 'tenant_settings_tenant_name_idx');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['tenant_id', 'status', 'trans_date'], 'transactions_tenant_status_date_idx');
            $table->index(['tenant_id', 'member_id', 'trans_date'], 'transactions_tenant_member_date_idx');
            $table->index(['tenant_id', 'savings_account_id', 'status', 'dr_cr'], 'transactions_tenant_account_status_type_idx');
        });

        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->index(['tenant_id', 'member_id', 'status'], 'savings_accounts_tenant_member_status_idx');
        });

        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'bank_transactions_tenant_status_idx');
            $table->index(['tenant_id', 'trans_date'], 'bank_transactions_tenant_date_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['tenant_id', 'expense_date'], 'expenses_tenant_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_tenant_date_idx');
        });

        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->dropIndex('bank_transactions_tenant_status_idx');
            $table->dropIndex('bank_transactions_tenant_date_idx');
        });

        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->dropIndex('savings_accounts_tenant_member_status_idx');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_tenant_status_date_idx');
            $table->dropIndex('transactions_tenant_member_date_idx');
            $table->dropIndex('transactions_tenant_account_status_type_idx');
        });

        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->dropIndex('tenant_settings_tenant_name_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_owner_read_created_idx');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_tenant_recipient_status_idx');
        });

        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->dropIndex('loan_repayments_tenant_status_date_idx');
            $table->dropIndex('loan_repayments_tenant_loan_status_idx');
        });

        Schema::table('withdraw_requests', function (Blueprint $table) {
            $table->dropIndex('withdraw_requests_tenant_status_idx');
        });

        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->dropIndex('deposit_requests_tenant_status_idx');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex('loans_tenant_status_idx');
            $table->dropIndex('loans_tenant_borrower_status_idx');
            $table->dropIndex('loans_tenant_branch_status_idx');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex('members_tenant_status_idx');
            $table->dropIndex('members_tenant_user_idx');
            $table->dropIndex('members_tenant_branch_status_idx');
        });
    }
};

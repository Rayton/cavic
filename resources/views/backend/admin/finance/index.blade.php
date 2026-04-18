@extends('layouts.app')

@section('content')
@php
    $depositRequests = $financeStats['deposit_requests'] ?? 0;
    $withdrawRequests = $financeStats['withdraw_requests'] ?? 0;
    $walletCount = $financeStats['wallet_records'] ?? 0;
    $bankAccounts = $financeStats['bank_accounts'] ?? 0;
    $todayTransactions = $financeStats['today_transactions'] ?? 0;
    $todayExpenses = $financeStats['today_expenses'] ?? 0;
    $pendingCashTransactionsCount = $financeStats['pending_cash_transactions'] ?? 0;
    $pendingBankTransactionsCount = $financeStats['pending_bank_transactions'] ?? 0;
@endphp
@include('backend.admin.partials.workspace-styles')
<style>
    .workspace-mini-table td, .workspace-mini-table th { vertical-align: middle; }
</style>

@include('backend.admin.partials.page-header', [
    'title' => _lang('Finance Workspace'),
    'subtitle' => _lang('Manage requests, transactions, banking, and finance exceptions for CAVIC from one grouped workspace.'),
    'badge' => _lang('Cash & Reconciliation'),
    'breadcrumbs' => [
        ['label' => _lang('Dashboard'), 'url' => route('dashboard.index')],
        ['label' => _lang('Finance Workspace'), 'active' => true],
    ],
    'actions' => [
        ['label' => _lang('New Transaction'), 'url' => route('transactions.create'), 'class' => 'btn-primary btn-sm'],
        ['label' => _lang('Open Wallets'), 'url' => route('wallets.index'), 'class' => 'btn-outline-primary btn-sm'],
    ],
])

<div class="row mb-4">
    <div class="col-md-4 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Deposit Requests') }}</div><div class="stat-value">{{ $depositRequests }}</div><a class="stat-link" href="{{ route('deposit_requests.index') }}">{{ _lang('Review deposits') }}</a></div></div></div>
    <div class="col-md-4 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Withdraw Requests') }}</div><div class="stat-value">{{ $withdrawRequests }}</div><a class="stat-link" href="{{ route('withdraw_requests.index') }}">{{ _lang('Review withdrawals') }}</a></div></div></div>
    <div class="col-md-4 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Pending Cash Transactions') }}</div><div class="stat-value">{{ $pendingCashTransactionsCount }}</div><a class="stat-link" href="{{ route('transactions.index') }}">{{ _lang('Review cash postings') }}</a></div></div></div>
    <div class="col-md-4 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Pending Bank Transactions') }}</div><div class="stat-value">{{ $pendingBankTransactionsCount }}</div><a class="stat-link" href="{{ route('bank_transactions.index') }}">{{ _lang('Open reconciliation queue') }}</a></div></div></div>
    <div class="col-md-4 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Wallet Records') }}</div><div class="stat-value">{{ number_format($walletCount) }}</div><a class="stat-link" href="{{ route('wallets.index') }}">{{ _lang('Open wallets') }}</a></div></div></div>
    <div class="col-md-4 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Bank Accounts') }}</div><div class="stat-value">{{ $bankAccounts }}</div><a class="stat-link" href="{{ route('bank_accounts.index') }}">{{ _lang('Manage banking') }}</a></div></div></div>
    <div class="col-md-4 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Today\'s Transactions') }}</div><div class="stat-value">{{ $todayTransactions }}</div><span class="text-muted small">{{ _lang('Operational posting activity for today') }}</span></div></div></div>
    <div class="col-md-4 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Today\'s Expenses') }}</div><div class="stat-value">{{ $todayExpenses }}</div><span class="text-muted small">{{ _lang('Expense entries posted today') }}</span></div></div></div>
</div>

<div class="card workspace-section-card">
    <div class="card-header">
        <ul class="nav nav-pills workspace-nav" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#wallets">{{ _lang('Wallets') }}</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#accounts">{{ _lang('Savings Accounts') }}</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#cash-ops">{{ _lang('Cash Transactions') }}</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#requests">{{ _lang('Requests') }}</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#exceptions">{{ _lang('Exceptions & Reconciliation') }}</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#expenses">{{ _lang('Expenses') }}</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#banking">{{ _lang('Banking') }}</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#methods">{{ _lang('Methods & Interest') }}</a></li>
        </ul>
    </div>
    <div class="card-body tab-content">
        <div class="tab-pane fade show active" id="wallets">
            <div class="row mb-4">
                @foreach($savingsProductSummary as $product)
                    <div class="col-md-6 col-xl-4 mb-3">
                        <div class="card workspace-bucket-card mb-0 h-100">
                            <div class="card-body">
                                <div class="bucket-label">{{ $product->name }}</div>
                                <div class="bucket-value">{{ $product->accounts_count }}</div>
                                <div class="bucket-meta">{{ _lang('Active accounts') }}{{ optional($product->currency)->name ? ' · '.optional($product->currency)->name : '' }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                <p class="text-muted mb-2 mb-md-0">{{ _lang('Wallet and contribution records remain available through the existing wallet tools.') }}</p>
                <a href="{{ route('wallets.index') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Open Wallets') }}</a>
            </div>
        </div>
        <div class="tab-pane fade" id="accounts">
            <div class="table-responsive">
                <table class="table table-sm table-bordered workspace-mini-table mb-3">
                    <thead>
                        <tr>
                            <th>{{ _lang('Account No') }}</th>
                            <th>{{ _lang('Member') }}</th>
                            <th>{{ _lang('Branch') }}</th>
                            <th>{{ _lang('Type') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSavingsAccounts as $account)
                            <tr>
                                <td>{{ $account->account_number }}</td>
                                <td>{{ $account->member->name }}</td>
                                <td>{{ optional($account->member->branch)->name }}</td>
                                <td>{{ $account->savings_type->name }}</td>
                                <td><a class="btn btn-light btn-xs" href="{{ route('savings_accounts.index') }}">{{ _lang('Open') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">{{ _lang('No recent savings accounts found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('savings_accounts.index') }}">{{ _lang('Member Accounts') }}</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('savings_products.index') }}">{{ _lang('Account Types') }}</a>
            </div>
        </div>
        <div class="tab-pane fade" id="cash-ops">
            <div class="table-responsive">
                <table class="table table-sm table-bordered workspace-mini-table mb-3">
                    <thead>
                        <tr>
                            <th>{{ _lang('Date') }}</th>
                            <th>{{ _lang('Member') }}</th>
                            <th>{{ _lang('Type') }}</th>
                            <th>{{ _lang('Amount') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->trans_date }}</td>
                                <td>{{ $transaction->member->name }}</td>
                                <td>{{ $transaction->type }}</td>
                                <td>{{ decimalPlace($transaction->amount, optional(optional($transaction->account)->savings_type->currency)->name) }}</td>
                                <td>{!! xss_clean(transaction_status($transaction->status)) !!}</td>
                                <td><a class="btn btn-light btn-xs" href="{{ route('transactions.show', $transaction->id) }}">{{ _lang('View') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">{{ _lang('No recent transactions found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('transactions.index') }}">{{ _lang('Transaction History') }}</a>
                <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('transactions.create') }}">{{ _lang('New Transaction') }}</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('transaction_categories.index') }}">{{ _lang('Transaction Categories') }}</a>
            </div>
        </div>
        <div class="tab-pane fade" id="requests">
            <div class="alert alert-light border small mb-3">
                <strong>{{ _lang('Finance queue') }}:</strong> {{ _lang('Short-cycle movement requests can be reviewed quickly from modal detail views without leaving this workspace.') }}
            </div>
            <div class="row">
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <h6>{{ _lang('Pending Deposit Requests') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table mb-3">
                            <thead><tr><th>{{ _lang('Member') }}</th><th>{{ _lang('Amount') }}</th><th>{{ _lang('Method') }}</th><th>{{ _lang('Status') }}</th><th>{{ _lang('Action') }}</th></tr></thead>
                            <tbody>
                                @forelse($pendingDeposits as $request)
                                    <tr>
                                        <td>{{ $request->member->name }}</td>
                                        <td>{{ decimalPlace($request->amount, optional(optional(optional($request->account)->savings_type)->currency)->name) }}</td>
                                        <td>{{ $request->method->name }}</td>
                                        <td><span class="workspace-status-chip pending">{{ _lang('Pending') }}</span></td>
                                        <td><a class="btn btn-light btn-xs ajax-modal" data-title="{{ _lang('Deposit Request Details') }}" href="{{ route('deposit_requests.show', $request->id) }}">{{ _lang('Quick View') }}</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No pending deposit requests') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h6>{{ _lang('Pending Withdraw Requests') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table mb-3">
                            <thead><tr><th>{{ _lang('Member') }}</th><th>{{ _lang('Amount') }}</th><th>{{ _lang('Method') }}</th><th>{{ _lang('Status') }}</th><th>{{ _lang('Action') }}</th></tr></thead>
                            <tbody>
                                @forelse($pendingWithdraws as $request)
                                    <tr>
                                        <td>{{ $request->member->name }}</td>
                                        <td>{{ decimalPlace($request->amount, optional(optional(optional($request->account)->savings_type)->currency)->name) }}</td>
                                        <td>{{ $request->method->name }}</td>
                                        <td><span class="workspace-status-chip pending">{{ _lang('Pending') }}</span></td>
                                        <td><a class="btn btn-light btn-xs ajax-modal" data-title="{{ _lang('Withdraw Request Details') }}" href="{{ route('withdraw_requests.show', $request->id) }}">{{ _lang('Quick View') }}</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No pending withdraw requests') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('deposit_requests.index') }}">{{ _lang('Deposit Requests') }}</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('withdraw_requests.index') }}">{{ _lang('Withdraw Requests') }}</a>
            </div>
        </div>
        <div class="tab-pane fade" id="exceptions">
            <div class="row mb-4">
                @foreach($financeExceptionCards as $card)
                    <div class="col-md-6 col-xl-3 mb-3">
                        <a href="{{ $card['route'] }}" class="text-reset text-decoration-none">
                            <div class="card workspace-bucket-card mb-0">
                                <div class="card-body">
                                    <div class="bucket-label">{{ $card['label'] }}</div>
                                    <div class="bucket-value">{{ number_format($card['value']) }}</div>
                                    <div class="bucket-meta">{{ $card['description'] }}</div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="workspace-section-title">{{ _lang('Branch Finance Pressure') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table mb-0">
                            <thead><tr><th>{{ _lang('Branch') }}</th><th>{{ _lang('Pending Deposits') }}</th><th>{{ _lang('Pending Withdraws') }}</th><th>{{ _lang('Pending Cash Transactions') }}</th><th>{{ _lang('Pressure Score') }}</th></tr></thead>
                            <tbody>
                                @forelse($branchFinancePressure as $branch)
                                    <tr>
                                        <td>{{ $branch->name }}</td>
                                        <td>{{ $branch->pending_deposits }}</td>
                                        <td>{{ $branch->pending_withdraws }}</td>
                                        <td>{{ $branch->pending_cash_transactions }}</td>
                                        <td><span class="workspace-status-chip {{ $branch->pressure_score > 0 ? 'review' : 'active' }}">{{ $branch->pressure_score }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No branch finance pressure data found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="workspace-section-title">{{ _lang('Pending Cash Transactions') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table mb-3">
                            <thead><tr><th>{{ _lang('Date') }}</th><th>{{ _lang('Member') }}</th><th>{{ _lang('Type') }}</th><th>{{ _lang('Amount') }}</th><th>{{ _lang('Action') }}</th></tr></thead>
                            <tbody>
                                @forelse($pendingCashTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->trans_date }}</td>
                                        <td>{{ $transaction->member->name }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $transaction->type)) }}</td>
                                        <td>{{ decimalPlace($transaction->amount, optional(optional($transaction->account)->savings_type->currency)->name) }}</td>
                                        <td><a class="btn btn-light btn-xs" href="{{ route('transactions.show', $transaction->id) }}">{{ _lang('View') }}</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No pending cash transactions') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="workspace-section-title">{{ _lang('Pending Bank Transactions') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table mb-3">
                            <thead><tr><th>{{ _lang('Date') }}</th><th>{{ _lang('Bank Account') }}</th><th>{{ _lang('Type') }}</th><th>{{ _lang('Amount') }}</th><th>{{ _lang('Action') }}</th></tr></thead>
                            <tbody>
                                @forelse($pendingBankTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->trans_date }}</td>
                                        <td>{{ $transaction->bank_account->account_name ?? $transaction->bank_account->account_number }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $transaction->type)) }}</td>
                                        <td>{{ money_format_2($transaction->amount) }}</td>
                                        <td><a class="btn btn-light btn-xs ajax-modal" data-title="{{ _lang('Bank Transaction Details') }}" href="{{ route('bank_transactions.show', $transaction->id) }}">{{ _lang('Quick View') }}</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No pending bank transactions') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="expenses">
            <div class="list-group workspace-link-list">
                <a class="list-group-item list-group-item-action" href="{{ route('expenses.index') }}">{{ _lang('Expenses') }}</a>
                <a class="list-group-item list-group-item-action" href="{{ route('expense_categories.index') }}">{{ _lang('Expense Categories') }}</a>
            </div>
        </div>
        <div class="tab-pane fade" id="banking">
            <div class="table-responsive">
                <table class="table table-sm table-bordered workspace-mini-table mb-3">
                    <thead><tr><th>{{ _lang('Date') }}</th><th>{{ _lang('Bank Account') }}</th><th>{{ _lang('Type') }}</th><th>{{ _lang('Amount') }}</th><th>{{ _lang('Status') }}</th><th>{{ _lang('Action') }}</th></tr></thead>
                    <tbody>
                        @forelse($latestBankTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->trans_date }}</td>
                                <td>{{ $transaction->bank_account->account_name ?? $transaction->bank_account->account_number }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $transaction->type)) }}</td>
                                <td>{{ money_format_2($transaction->amount) }}</td>
                                <td>{!! $transaction->status == 0 ? xss_clean(show_status(_lang('Pending'), 'danger')) : xss_clean(show_status(_lang('Completed'), 'success')) !!}</td>
                                <td><a class="btn btn-light btn-xs ajax-modal" data-title="{{ _lang('Bank Transaction Details') }}" href="{{ route('bank_transactions.show', $transaction->id) }}">{{ _lang('Quick View') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">{{ _lang('No recent bank transactions found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('bank_accounts.index') }}">{{ _lang('Bank Accounts') }}</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('bank_transactions.index') }}">{{ _lang('Bank Transactions') }}</a>
            </div>
        </div>
        <div class="tab-pane fade" id="methods">
            <div class="row mb-4">
                <div class="col-md-6 col-xl-3 mb-3"><div class="card workspace-bucket-card mb-0"><div class="card-body"><div class="bucket-label">{{ _lang('Online Gateways') }}</div><div class="bucket-value">{{ $methodsSummary['automatic_gateways'] ?? 0 }}</div><div class="bucket-meta">{{ _lang('Configured automatic collection options') }}</div></div></div></div>
                <div class="col-md-6 col-xl-3 mb-3"><div class="card workspace-bucket-card mb-0"><div class="card-body"><div class="bucket-label">{{ _lang('Deposit Methods') }}</div><div class="bucket-value">{{ $methodsSummary['deposit_methods'] ?? 0 }}</div><div class="bucket-meta">{{ _lang('Manual deposit intake methods') }}</div></div></div></div>
                <div class="col-md-6 col-xl-3 mb-3"><div class="card workspace-bucket-card mb-0"><div class="card-body"><div class="bucket-label">{{ _lang('Withdraw Methods') }}</div><div class="bucket-value">{{ $methodsSummary['withdraw_methods'] ?? 0 }}</div><div class="bucket-meta">{{ _lang('Cash-out channels available') }}</div></div></div></div>
                <div class="col-md-6 col-xl-3 mb-3"><div class="card workspace-bucket-card mb-0"><div class="card-body"><div class="bucket-label">{{ _lang('Active Savings Products') }}</div><div class="bucket-value">{{ $methodsSummary['active_savings_products'] ?? 0 }}</div><div class="bucket-meta">{{ _lang('Products using interest and contribution rules') }}</div></div></div></div>
            </div>
            <div class="list-group workspace-link-list">
                <a class="list-group-item list-group-item-action" href="{{ route('automatic_methods.index') }}">{{ _lang('Online Gateways') }}</a>
                <a class="list-group-item list-group-item-action" href="{{ route('deposit_methods.index') }}">{{ _lang('Offline Gateways') }}</a>
                <a class="list-group-item list-group-item-action" href="{{ route('withdraw_methods.index') }}">{{ _lang('Withdraw Methods') }}</a>
                <a class="list-group-item list-group-item-action" href="{{ route('interest_calculation.calculator') }}">{{ _lang('Interest Calculation') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('workspace_top_tabs')
@include('backend.admin.partials.module-tabs', [
    'variant' => 'top-strip',
    'role' => 'navigation',
    'tabs' => [
        ['label' => _lang('Member Requests'), 'target' => '#member-requests', 'active' => true],
        ['label' => _lang('Pending Loans'), 'target' => '#pending-loans'],
        ['label' => _lang('Finance Requests'), 'target' => '#finance-requests'],
        ['label' => _lang('Due Today & Upcoming'), 'target' => '#due-upcoming'],
        ['label' => _lang('Exceptions'), 'target' => '#exceptions'],
    ],
])
@endsection

@section('content')
@php
    $memberRequestsCount = $actionStats['member_requests'] ?? request_count('member_requests');
    $pendingLoansCount = $actionStats['pending_loans'] ?? request_count('pending_loans');
    $depositRequestsCount = $actionStats['deposit_requests'] ?? request_count('deposit_requests');
    $withdrawRequestsCount = $actionStats['withdraw_requests'] ?? request_count('withdraw_requests');
    $dueTodayCount = $actionStats['due_today'] ?? 0;
    $upcomingRepaymentsCount = $actionStats['upcoming'] ?? 0;
    $overdueRepaymentsCount = $actionStats['overdue'] ?? 0;
    $readyForDisbursementCount = $actionStats['ready_disbursement'] ?? 0;
    $criticalCollectionsCount = $actionStats['critical_collections'] ?? 0;
    $pendingBankTransactionsCount = $actionStats['pending_bank_transactions'] ?? 0;
    $pendingCashTransactionsCount = $actionStats['pending_cash_transactions'] ?? 0;
    $pendingFinanceRequestsCount = $actionStats['pending_finance_requests'] ?? 0;
    $exceptionCount = $actionStats['exception_total'] ?? 0;
    $brokenPromisesCount = $actionStats['broken_promises'] ?? 0;
    $followUpsLoggedToday = $collectionExecutionStats['logged_today'] ?? 0;
    $promiseDueTodayCount = $collectionExecutionStats['promise_due_today'] ?? 0;
    $resolvedInRangeCount = $collectionExecutionStats['resolved_in_range'] ?? 0;
    $recoveredInRangeCount = $collectionExecutionStats['recovered_in_range'] ?? 0;
    $promiseKeptCount = $collectionExecutionStats['promise_kept'] ?? 0;
    $collectionCompletionRate = $collectionExecutionStats['completion_rate'] ?? 0;
@endphp
@include('backend.admin.partials.workspace-styles')
<style>
    .workspace-mini-table td, .workspace-mini-table th { vertical-align: middle; }
    .workspace-mini-table .btn { white-space: nowrap; }
</style>

@include('backend.admin.partials.page-header', [
    'title' => _lang('Action Center'),
    'subtitle' => _lang('Process urgent approvals, repayment pressure, and finance exceptions from one operational queue.'),
    'badge' => _lang('Operational Queue'),
    'breadcrumbs' => [
        ['label' => _lang('Dashboard'), 'url' => route('dashboard.index')],
        ['label' => _lang('Action Center'), 'active' => true],
    ],
])

@include('backend.admin.partials.collection-date-range-filter', ['collectionDateRange' => $collectionDateRange, 'filterId' => 'action-center-collection-range'])

<div class="row mb-4">
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Member Requests') }}</div><div class="stat-value">{{ $memberRequestsCount }}</div><a class="stat-link" href="{{ route('members.pending_requests') }}">{{ _lang('Open queue') }}</a></div></div>
    </div>
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Pending Loans') }}</div><div class="stat-value">{{ $pendingLoansCount }}</div><a class="stat-link" href="{{ route('loans.filter', 'pending') }}">{{ _lang('Review applications') }}</a></div></div>
    </div>
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Due Today') }}</div><div class="stat-value">{{ $dueTodayCount }}</div><a class="stat-link" href="{{ route('loans.workspace') }}">{{ _lang('Open today\'s collections') }}</a></div></div>
    </div>
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Deposit Requests') }}</div><div class="stat-value">{{ $depositRequestsCount }}</div><a class="stat-link" href="{{ route('deposit_requests.index') }}">{{ _lang('Open requests') }}</a></div></div>
    </div>
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Withdraw Requests') }}</div><div class="stat-value">{{ $withdrawRequestsCount }}</div><a class="stat-link" href="{{ route('withdraw_requests.index') }}">{{ _lang('Open requests') }}</a></div></div>
    </div>
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Overdue Repayments') }}</div><div class="stat-value">{{ $overdueRepaymentsCount }}</div><a class="stat-link" href="{{ route('loans.workspace') }}">{{ _lang('Review collections') }}</a></div></div>
    </div>
    <div class="col-md-4 col-xl mb-3">
        <div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Critical Collections') }}</div><div class="stat-value">{{ $criticalCollectionsCount }}</div><a class="stat-link" href="{{ route('loans.workspace') }}">{{ _lang('Escalate critical cases') }}</a></div></div>
    </div>
</div>

<div class="card workspace-section-card">
    <div class="card-body tab-content">
        <div class="tab-pane fade show active" id="member-requests">
            <div class="table-responsive">
                <table class="table table-sm table-bordered workspace-mini-table mb-3">
                    <thead>
                        <tr>
                            <th>{{ _lang('Member') }}</th>
                            <th>{{ _lang('Member No') }}</th>
                            <th>{{ _lang('Branch') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($memberRequests as $member)
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->member_no }}</td>
                                <td>{{ $member->branch->name }}</td>
                                <td><span class="workspace-status-chip pending">{{ _lang('Pending Approval') }}</span></td>
                                <td class="text-center">
                                    <a class="btn btn-light btn-xs" href="{{ route('members.show', $member->id) }}">{{ _lang('View') }}</a>
                                    <a class="btn btn-success btn-xs ajax-modal" href="{{ route('members.accept_request', $member->id) }}" data-title="{{ _lang('Approve Member Request') }}">{{ _lang('Approve') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">{{ _lang('No pending member requests') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('members.pending_requests') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Open Full Member Requests Queue') }}</a>
        </div>
        <div class="tab-pane fade" id="pending-loans">
            <div class="table-responsive">
                <table class="table table-sm table-bordered workspace-mini-table mb-3">
                    <thead>
                        <tr>
                            <th>{{ _lang('Loan ID') }}</th>
                            <th>{{ _lang('Borrower') }}</th>
                            <th>{{ _lang('Product') }}</th>
                            <th>{{ _lang('Amount') }}</th>
                            <th>{{ _lang('Stage') }}</th>
                            <th>{{ _lang('Approval Progress') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingLoans as $loan)
                            <tr>
                                <td>{{ $loan->loan_id }}</td>
                                <td>{{ $loan->borrower->name }}</td>
                                <td>{{ $loan->loan_product->name }}</td>
                                <td>{{ decimalPlace($loan->applied_amount, optional($loan->currency)->name) }}</td>
                                <td><span class="workspace-status-chip {{ $loan->workspace_stage_theme ?? 'review' }}">{{ $loan->workspace_stage_label ?? _lang('Under Review') }}</span></td>
                                <td><span class="text-muted small">{{ $loan->workspace_stage_meta ?? _lang('Waiting in queue') }}</span></td>
                                <td class="text-center"><a class="btn btn-light btn-xs" href="{{ route('loans.show', $loan->id) }}">{{ _lang('Review') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">{{ _lang('No pending loans') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <a href="{{ route('loans.filter', 'pending') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Open Full Pending Loans Queue') }}</a>
        </div>
        <div class="tab-pane fade" id="finance-requests">
            <div class="row">
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <h6>{{ _lang('Deposit Requests') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table mb-3">
                            <thead><tr><th>{{ _lang('Member') }}</th><th>{{ _lang('Amount') }}</th><th>{{ _lang('Method') }}</th><th>{{ _lang('Status') }}</th><th>{{ _lang('Action') }}</th></tr></thead>
                            <tbody>
                                @forelse($depositRequests as $request)
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
                    <a href="{{ route('deposit_requests.index') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Open Deposit Requests') }}</a>
                </div>
                <div class="col-lg-6">
                    <h6>{{ _lang('Withdraw Requests') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table mb-3">
                            <thead><tr><th>{{ _lang('Member') }}</th><th>{{ _lang('Amount') }}</th><th>{{ _lang('Method') }}</th><th>{{ _lang('Status') }}</th><th>{{ _lang('Action') }}</th></tr></thead>
                            <tbody>
                                @forelse($withdrawRequests as $request)
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
                    <a href="{{ route('withdraw_requests.index') }}" class="btn btn-outline-primary btn-sm">{{ _lang('Open Withdraw Requests') }}</a>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="due-upcoming">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="workspace-section-title">{{ _lang('Due Today') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table mb-3">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Repayment Date') }}</th>
                                    <th>{{ _lang('Amount') }}</th>
                                    <th>{{ _lang('Status') }}</th>
                                    <th>{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dueTodayRepayments as $repayment)
                                    <tr>
                                        <td>{{ $repayment->loan->loan_id }}</td>
                                        <td>{{ $repayment->loan->borrower->name }}</td>
                                        <td>{{ $repayment->repayment_date }}</td>
                                        <td>{{ decimalPlace($repayment->amount_to_pay, optional($repayment->loan->currency)->name) }}</td>
                                        <td><span class="workspace-status-chip today">{{ _lang('Due Today') }}</span></td>
                                        <td><a class="btn btn-light btn-xs" href="{{ route('loans.show', $repayment->loan_id) }}">{{ _lang('Loan') }}</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">{{ _lang('No repayments due today') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="workspace-section-title">{{ _lang('Next 7 Days') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table mb-3">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Repayment Date') }}</th>
                                    <th>{{ _lang('Amount') }}</th>
                                    <th>{{ _lang('Status') }}</th>
                                    <th>{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingRepayments as $repayment)
                                    <tr>
                                        <td>{{ $repayment->loan->loan_id }}</td>
                                        <td>{{ $repayment->loan->borrower->name }}</td>
                                        <td>{{ $repayment->repayment_date }}</td>
                                        <td>{{ decimalPlace($repayment->amount_to_pay, optional($repayment->loan->currency)->name) }}</td>
                                        <td><span class="workspace-status-chip upcoming">{{ _lang('Upcoming') }}</span></td>
                                        <td><a class="btn btn-light btn-xs" href="{{ route('loans.show', $repayment->loan_id) }}">{{ _lang('Loan') }}</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">{{ _lang('No upcoming repayments in the next 7 days') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <div class="workspace-section-title">{{ _lang('Collector-ready Call List') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Phone') }}</th>
                                    <th>{{ _lang('Queue') }}</th>
                                    <th>{{ _lang('Last Follow-up') }}</th>
                                    <th>{{ _lang('Next Action') }}</th>
                                    <th>{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($collectorReadyCallList as $item)
                                    <tr>
                                        <td>{{ $item->loan_id }}</td>
                                        <td>{{ $item->borrower_name }}</td>
                                        <td>{{ trim($item->contact_phone) != '' ? $item->contact_phone : _lang('N/A') }}</td>
                                        <td><span class="workspace-status-chip {{ $item->queue_theme }}">{{ $item->queue_label }}</span></td>
                                        <td>
                                            @if($item->last_outcome_label)
                                                <span class="workspace-status-chip {{ $item->last_outcome_theme }}">{{ $item->last_outcome_label }}</span>
                                            @else
                                                <span class="text-muted small">{{ _lang('No log yet') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->next_action }}</td>
                                        <td><a class="btn btn-light btn-xs ajax-modal" data-title="{{ _lang('Log Collection Follow-up') }}" href="{{ route('loan_collection_follow_ups.create', $item->repayment_id) }}">{{ _lang('Log') }}</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">{{ _lang('No call list items available') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="workspace-section-title">{{ _lang('Upcoming Reminder Queue') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Due In') }}</th>
                                    <th>{{ _lang('Reminder') }}</th>
                                    <th>{{ _lang('Last Follow-up') }}</th>
                                    <th>{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingReminderQueue as $item)
                                    <tr>
                                        <td>{{ $item->loan_id }}</td>
                                        <td>{{ $item->borrower_name }}</td>
                                        <td>{{ $item->days_until }} {{ _lang('days') }}</td>
                                        <td><span class="workspace-status-chip {{ $item->reminder_theme }}">{{ $item->reminder_label }}</span></td>
                                        <td>
                                            @if($item->last_outcome_label)
                                                <span class="workspace-status-chip {{ $item->last_outcome_theme }}">{{ $item->last_outcome_label }}</span>
                                            @else
                                                <span class="text-muted small">{{ _lang('No log yet') }}</span>
                                            @endif
                                        </td>
                                        <td><a class="btn btn-light btn-xs ajax-modal" data-title="{{ _lang('Log Collection Follow-up') }}" href="{{ route('loan_collection_follow_ups.create', $item->repayment_id) }}">{{ _lang('Log') }}</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">{{ _lang('No reminder items available') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <a href="{{ route('loans.upcoming_loan_repayments') }}" class="btn btn-outline-primary btn-sm mt-4">{{ _lang('Open Full Repayment Schedule') }}</a>
        </div>
        <div class="tab-pane fade" id="exceptions">
            <div class="alert alert-light border mb-4">
                {{ _lang('Current high-priority exception queue') }}: <strong>{{ $exceptionCount }}</strong>
            </div>
            <div class="alert alert-light small mb-4">
                <strong>{{ _lang('Selected analytics range') }}:</strong> {{ $collectionDateRange['label'] ?? _lang('Today') }}
            </div>
            @unless($followUpsEnabled)
                <div class="alert alert-info small">
                    <strong>{{ _lang('Follow-up tracking setup') }}:</strong> {{ _lang('Run the latest migration to unlock saved collection outcomes, broken-promise tracking, and branch completion analytics.') }}
                </div>
            @endunless
            <div class="row mb-4">
                <div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Follow-ups Logged') }}</div><div class="bucket-value">{{ $followUpsLoggedToday }}</div><div class="bucket-meta">{{ _lang('Collection notes recorded in selected range') }}</div></div></div></div>
                <div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Promises in Range') }}</div><div class="bucket-value">{{ $promiseDueTodayCount }}</div><div class="bucket-meta">{{ _lang('Promised-to-pay cases dated inside selected range') }}</div></div></div></div>
                <div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Broken Promises') }}</div><div class="bucket-value">{{ $brokenPromisesCount }}</div><div class="bucket-meta">{{ _lang('Outstanding promised-to-pay cases already past due as of range end') }}</div></div></div></div>
                <div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Recovered') }}</div><div class="bucket-value">{{ $recoveredInRangeCount }}</div><div class="bucket-meta">{{ _lang('Paid repayments that had follow-up activity in selected range') }}</div></div></div></div>
                <div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Promise Kept') }}</div><div class="bucket-value">{{ $promiseKeptCount }}</div><div class="bucket-meta">{{ _lang('Promised-to-pay cases settled on or before committed date') }}</div></div></div></div>
                <div class="col-md-4 col-xl-2 mb-3"><div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Completion Rate') }}</div><div class="bucket-value">{{ $collectionCompletionRate }}%</div><div class="bucket-meta">{{ _lang('Open due and overdue cases touched in selected range') }}</div></div></div></div>
            </div>
            <div class="row mb-4">
                @foreach($collectionBuckets as $bucket)
                    <div class="col-md-4 mb-3">
                        <div class="card workspace-bucket-card mb-0">
                            <div class="card-body">
                                <div class="bucket-label">{{ $bucket->label }}</div>
                                <div class="bucket-value">{{ $bucket->count }}</div>
                                <div class="bucket-meta">{{ _lang('Collections aging bucket') }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="row">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <ul class="workspace-exception-list">
                        <li><span>{{ _lang('Overdue repayments') }}</span><strong>{{ $overdueRepaymentsCount }}</strong></li>
                        <li><span>{{ _lang('Ready for disbursement') }}</span><strong>{{ $readyForDisbursementCount }}</strong></li>
                        <li><span>{{ _lang('Pending finance requests') }}</span><strong>{{ $pendingFinanceRequestsCount }}</strong></li>
                        <li><span>{{ _lang('Pending bank transactions') }}</span><strong>{{ $pendingBankTransactionsCount }}</strong></li>
                        <li><span>{{ _lang('Pending cash transactions') }}</span><strong>{{ $pendingCashTransactionsCount }}</strong></li>
                        <li><span>{{ _lang('Broken promises') }}</span><strong>{{ $brokenPromisesCount }}</strong></li>
                        <li><span>{{ _lang('Recovered in range') }}</span><strong>{{ $recoveredInRangeCount }}</strong></li>
                        <li><span>{{ _lang('Promise kept') }}</span><strong>{{ $promiseKeptCount }}</strong></li>
                    </ul>
                    <div class="workspace-section-title mt-4">{{ _lang('Branch Collections Pressure') }}</div>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered workspace-mini-table table-export mb-0" data-export-filename="Action_Center_Branch_Collections_Pressure">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Due Today') }}</th>
                                    <th>{{ _lang('Overdue') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchCollectionsPressure as $branch)
                                    <tr>
                                        <td>{{ $branch->name }}</td>
                                        <td>{{ $branch->due_today }}</td>
                                        <td>{{ $branch->overdue }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">{{ _lang('No branch collections pressure found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="workspace-section-title">{{ _lang('Branch Follow-up Performance') }}</div>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered workspace-mini-table table-export mb-0" data-export-filename="Action_Center_Branch_Follow_Up_Performance">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Open Queue') }}</th>
                                    <th>{{ _lang('Touched') }}</th>
                                    <th>{{ _lang('Resolved') }}</th>
                                    <th>{{ _lang('Completion') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchFollowUpPerformance as $branch)
                                    <tr>
                                        <td>{{ $branch->name }}</td>
                                        <td>{{ $branch->open_queue }}</td>
                                        <td>{{ $branch->touched_today }}</td>
                                        <td>{{ $branch->resolved_in_range }}</td>
                                        <td><span class="workspace-status-chip {{ $branch->completion_rate >= 70 ? 'active' : ($branch->completion_rate >= 40 ? 'review' : 'critical') }}">{{ $branch->completion_rate }}%</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No branch follow-up performance yet') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="workspace-section-title">{{ _lang('Collector Follow-up Performance') }}</div>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered workspace-mini-table table-export mb-0" data-export-filename="Action_Center_Collector_Follow_Up_Performance">
                            <thead>
                                <tr>
                                    <th>{{ _lang('User') }}</th>
                                    <th>{{ _lang('Logs') }}</th>
                                    <th>{{ _lang('Cases') }}</th>
                                    <th>{{ _lang('Resolved') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($collectorFollowUpPerformance as $collector)
                                    <tr>
                                        <td>{{ $collector->name }}</td>
                                        <td>{{ $collector->logs_count }}</td>
                                        <td>{{ $collector->cases_touched }}</td>
                                        <td>{{ $collector->resolved_count }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">{{ _lang('No collector follow-up activity yet') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="workspace-section-title">{{ _lang('Recent Resolutions') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table table-export mb-0" data-export-filename="Action_Center_Recent_Resolutions">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Paid On') }}</th>
                                    <th>{{ _lang('Resolution') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentResolvedCases as $item)
                                    <tr>
                                        <td>{{ $item->loan_id }}</td>
                                        <td>{{ $item->borrower_name }}</td>
                                        <td>{{ $item->payment_date }}</td>
                                        <td><span class="workspace-status-chip {{ $item->resolution_theme }}">{{ $item->resolution_label }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">{{ _lang('No resolved follow-up cases found for this range') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="workspace-section-title">{{ _lang('Collections Priority Queue') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table table-export mb-0" data-export-filename="Action_Center_Collections_Priority_Queue">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Phone') }}</th>
                                    <th>{{ _lang('Aging') }}</th>
                                    <th>{{ _lang('Last Follow-up') }}</th>
                                    <th>{{ _lang('Next Action') }}</th>
                                    <th>{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($collectionPriorityQueue as $item)
                                    <tr>
                                        <td>{{ $item->loan_id }}</td>
                                        <td>{{ $item->borrower_name }}</td>
                                        <td>{{ $item->branch_name }}</td>
                                        <td>{{ trim($item->contact_phone) != '' ? $item->contact_phone : _lang('N/A') }}</td>
                                        <td>{{ $item->days_late }} {{ _lang('days late') }}</td>
                                        <td>
                                            @if($item->last_outcome_label)
                                                <span class="workspace-status-chip {{ $item->last_outcome_theme }}">{{ $item->last_outcome_label }}</span>
                                            @else
                                                <span class="text-muted small">{{ _lang('No log yet') }}</span>
                                            @endif
                                        </td>
                                        <td><span class="workspace-status-chip {{ $item->action_theme }}">{{ $item->action_label }}</span></td>
                                        <td><a class="btn btn-light btn-xs ajax-modal" data-title="{{ _lang('Log Collection Follow-up') }}" href="{{ route('loan_collection_follow_ups.create', $item->repayment_id) }}">{{ _lang('Log') }}</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted">{{ _lang('No overdue repayments') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="workspace-section-title mt-4">{{ _lang('Promise Follow-up Queue') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table table-export mb-0" data-export-filename="Action_Center_Promise_Follow_Up_Queue">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Promise Date') }}</th>
                                    <th>{{ _lang('Status') }}</th>
                                    <th>{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($promiseFollowUpQueue as $item)
                                    <tr>
                                        <td>{{ $item->loan_id }}</td>
                                        <td>{{ $item->borrower_name }}</td>
                                        <td>{{ $item->promised_payment_date }}</td>
                                        <td><span class="workspace-status-chip {{ $item->promise_status_theme }}">{{ $item->promise_status_label }}</span></td>
                                        <td><a class="btn btn-light btn-xs ajax-modal" data-title="{{ _lang('Log Collection Follow-up') }}" href="{{ route('loan_collection_follow_ups.create', $item->repayment_id) }}">{{ _lang('Log') }}</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No promise follow-up items found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

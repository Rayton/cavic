@extends('layouts.app')

@section('workspace_top_tabs')
@include('backend.admin.partials.module-tabs', [
    'variant' => 'top-strip',
    'role' => 'navigation',
    'tabs' => [
        ['label' => _lang('Pipeline'), 'target' => '#pipeline', 'active' => true],
        ['label' => _lang('Disbursements'), 'target' => '#disbursements'],
        ['label' => _lang('Repayments'), 'target' => '#repayments'],
        ['label' => _lang('Due Today & Upcoming'), 'target' => '#due-upcoming'],
        ['label' => _lang('Collections'), 'target' => '#collections'],
        ['label' => _lang('Products & Tools'), 'target' => '#products'],
    ],
])
@endsection

@section('content')
@php
    $pendingLoansCount = $loanStats['pending'] ?? 0;
    $dueTodayRepaymentsCount = $loanStats['due_today'] ?? 0;
    $upcomingRepaymentsCount = $loanStats['upcoming'] ?? 0;
    $activeLoansCount = $loanStats['active'] ?? 0;
    $loanProductsCount = $loanStats['products'] ?? 0;
    $overdueRepaymentsCount = $loanStats['overdue'] ?? 0;
    $readyForDisbursementCount = $loanStats['ready_disbursement'] ?? 0;
    $criticalCollectionsCount = $loanStats['critical_collections'] ?? 0;
    $followUpsLoggedToday = $collectionExecutionStats['logged_today'] ?? 0;
    $promiseDueTodayCount = $collectionExecutionStats['promise_due_today'] ?? 0;
    $brokenPromisesCount = $collectionExecutionStats['broken_promises'] ?? 0;
    $resolvedInRangeCount = $collectionExecutionStats['resolved_in_range'] ?? 0;
    $recoveredInRangeCount = $collectionExecutionStats['recovered_in_range'] ?? 0;
    $promiseKeptCount = $collectionExecutionStats['promise_kept'] ?? 0;
    $collectionCompletionRate = $collectionExecutionStats['completion_rate'] ?? 0;
@endphp
@include('backend.admin.partials.workspace-styles')
<style>
    .workspace-mini-table td, .workspace-mini-table th { vertical-align: middle; }
    .loan-tools-layout { display: grid; grid-template-columns: minmax(210px, 260px) minmax(0, 1fr); gap: 1.5rem; }
    .loan-tools-sidebar { border-right: 1px solid #e4ecec; padding-right: 1rem; }
    .loan-tools-sidebar .nav-link { border-radius: 8px; color: #2d3a45; font-weight: 600; margin-bottom: .35rem; padding: .8rem .9rem; }
    .loan-tools-sidebar .nav-link.active { background: #315f65; color: #fff; }
    .loan-tools-sidebar .tool-meta { display: block; font-size: .74rem; font-weight: 400; line-height: 1.25; opacity: .72; }
    .loan-tools-panel-header { align-items: center; border-bottom: 1px solid #e8eeee; display: flex; flex-wrap: wrap; gap: .75rem; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; }
    .loan-tools-panel-title { font-size: 1rem; font-weight: 700; margin-bottom: .15rem; }
    .loan-tools-panel-copy { color: #6b7280; font-size: .84rem; margin: 0; }
    .loan-tools-empty { border: 1px dashed #cbdada; border-radius: 8px; color: #6b7280; padding: 1.5rem; text-align: center; }
    .loan-calculator-launch { background: #f7faf9; border: 1px solid #dbe6e6; border-radius: 8px; padding: 1.25rem; }
    @media (max-width: 991.98px) {
        .loan-tools-layout { grid-template-columns: 1fr; }
        .loan-tools-sidebar { border-right: 0; border-bottom: 1px solid #e4ecec; padding: 0 0 1rem; }
    }
</style>

@include('backend.admin.partials.page-header', [
    'title' => _lang('Loans Workspace'),
    'subtitle' => _lang('Track the CAVIC loan pipeline, disbursement readiness, due schedules, and collections pressure from one workspace.'),
    'badge' => _lang('Portfolio Operations'),
    'breadcrumbs' => [
        ['label' => _lang('Dashboard'), 'url' => route('dashboard.index')],
        ['label' => _lang('Loans Workspace'), 'active' => true],
    ],
    'actions' => [
        ['label' => _lang('Post Repayment'), 'url' => route('loan_payments.create'), 'class' => 'btn-outline-primary btn-sm'],
    ],
])

@include('backend.admin.partials.collection-date-range-filter', ['collectionDateRange' => $collectionDateRange, 'filterId' => 'loan-workspace-collection-range'])

<div class="workspace-first-tab-stats" data-tab="#pipeline">
<div class="row mb-4">
    <div class="col-md-6 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Pending Loans') }}</div><div class="stat-value">{{ $pendingLoansCount }}</div><a class="stat-link" href="{{ route('loans.filter', 'pending') }}">{{ _lang('Review queue') }}</a></div></div></div>
    <div class="col-md-6 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Ready for Disbursement') }}</div><div class="stat-value">{{ $readyForDisbursementCount }}</div><span class="text-muted small">{{ _lang('Approved and waiting to be released') }}</span></div></div></div>
    <div class="col-md-6 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Due Today') }}</div><div class="stat-value">{{ $dueTodayRepaymentsCount }}</div><span class="text-muted small">{{ _lang('Collections that require action today') }}</span></div></div></div>
    <div class="col-md-6 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Upcoming Repayments') }}</div><div class="stat-value">{{ $upcomingRepaymentsCount }}</div><a class="stat-link" href="{{ route('loans.upcoming_loan_repayments') }}">{{ _lang('Open schedule') }}</a></div></div></div>
    <div class="col-md-6 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Overdue Repayments') }}</div><div class="stat-value">{{ $overdueRepaymentsCount }}</div><span class="text-muted small">{{ _lang('Collections follow-up required') }}</span></div></div></div>
    <div class="col-md-6 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Critical Collections') }}</div><div class="stat-value">{{ $criticalCollectionsCount }}</div><span class="text-muted small">{{ _lang('31+ day cases needing escalation') }}</span></div></div></div>
    <div class="col-md-6 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Active Loans') }}</div><div class="stat-value">{{ $activeLoansCount }}</div><a class="stat-link" href="{{ route('loans.filter', 'active') }}">{{ _lang('Open active loans') }}</a></div></div></div>
    <div class="col-md-6 col-xl mb-3"><div class="card workspace-stat-card mb-0"><div class="card-body"><div class="stat-label">{{ _lang('Loan Products') }}</div><div class="stat-value">{{ $loanProductsCount }}</div><a class="stat-link" href="#products" data-toggle="tab">{{ _lang('Manage products') }}</a></div></div></div>
</div>
</div>

<div class="card workspace-section-card">
    <div class="card-body tab-content">
        <div class="tab-pane fade show active" id="pipeline">
            <div class="table-responsive">
                <table class="table table-sm table-bordered workspace-mini-table mb-3">
                    <thead>
                        <tr>
                            <th>{{ _lang('Loan ID') }}</th>
                            <th>{{ _lang('Borrower') }}</th>
                            <th>{{ _lang('Product') }}</th>
                            <th>{{ _lang('Amount') }}</th>
                            <th>{{ _lang('Stage') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingLoans as $loan)
                            <tr>
                                <td>{{ $loan->loan_id }}</td>
                                <td>{{ $loan->borrower->name }}</td>
                                <td>{{ $loan->loan_product->name }}</td>
                                <td>{{ decimalPlace($loan->applied_amount, optional($loan->currency)->name) }}</td>
                                <td>
                                    <span class="workspace-status-chip {{ $loan->workspace_stage_theme ?? 'review' }}">{{ $loan->workspace_stage_label ?? _lang('Under Review') }}</span>
                                    <div class="small text-muted mt-1">{{ $loan->workspace_stage_meta ?? _lang('Waiting in queue') }}</div>
                                </td>
                                <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Review'), 'url' => route('loans.show', $loan->id), 'icon' => 'ti-eye']]])</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">{{ _lang('No pending loans in queue') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('loans.index') }}">{{ _lang('All Loans') }}</a>
                <a class="btn btn-outline-primary btn-sm mr-2" href="{{ route('loans.filter', 'pending') }}">{{ _lang('Pending Loans') }}</a>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('loans.filter', 'active') }}">{{ _lang('Active Loans') }}</a>
            </div>
        </div>
        <div class="tab-pane fade" id="disbursements">
            <div class="row mb-4">
                @foreach($approvalBottlenecks as $bottleneck)
                    <div class="col-md-3 mb-3">
                        <div class="card workspace-bucket-card mb-0">
                            <div class="card-body">
                                <div class="bucket-label">{{ $bottleneck->label }}</div>
                                <div class="bucket-value">{{ $bottleneck->count }}</div>
                                <div class="bucket-meta">{{ _lang('Loans waiting at this approval step') }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="workspace-section-title">{{ _lang('Ready for Disbursement') }}</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered workspace-mini-table mb-4">
                    <thead>
                        <tr>
                            <th>{{ _lang('Loan ID') }}</th>
                            <th>{{ _lang('Borrower') }}</th>
                            <th>{{ _lang('Product') }}</th>
                            <th>{{ _lang('Amount') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($readyForDisbursement as $loan)
                            <tr>
                                <td>{{ $loan->loan_id }}</td>
                                <td>{{ $loan->borrower->name }}</td>
                                <td>{{ $loan->loan_product->name }}</td>
                                <td>{{ decimalPlace($loan->applied_amount, optional($loan->currency)->name) }}</td>
                                <td><span class="workspace-status-chip ready">{{ _lang('Ready') }}</span></td>
                                <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Open'), 'url' => route('loans.show', $loan->id), 'icon' => 'ti-eye']]])</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">{{ _lang('No loans currently marked as ready for disbursement') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="workspace-section-title">{{ _lang('Blocked Before Disbursement') }}</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered workspace-mini-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ _lang('Loan ID') }}</th>
                            <th>{{ _lang('Borrower') }}</th>
                            <th>{{ _lang('Product') }}</th>
                            <th>{{ _lang('Amount') }}</th>
                            <th>{{ _lang('Progress') }}</th>
                            <th>{{ _lang('Blocker') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($approvalBlockers as $loan)
                            <tr>
                                <td>{{ $loan->loan_id }}</td>
                                <td>{{ $loan->borrower_name }}</td>
                                <td>{{ $loan->product_name }}</td>
                                <td>{{ $loan->amount }}</td>
                                <td>{{ $loan->progress }}</td>
                                <td><span class="workspace-status-chip {{ $loan->blocker_theme }}">{{ $loan->blocker_label }}</span></td>
                                <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Review'), 'url' => route('loans.show', $loan->id), 'icon' => 'ti-eye']]])</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">{{ _lang('No blocked approvals at the moment') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="repayments">
            <div class="list-group workspace-link-list">
                <a class="list-group-item list-group-item-action" href="{{ route('loan_payments.index') }}">{{ _lang('Loan Repayments') }}</a>
                <a class="list-group-item list-group-item-action" href="{{ route('loan_payments.create') }}">{{ _lang('Add Repayment') }}</a>
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
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No repayments due today') }}</td></tr>
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
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No upcoming repayments found') }}</td></tr>
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
                                        <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Log Follow-up'), 'url' => route('loan_collection_follow_ups.create', $item->repayment_id), 'icon' => 'ti-write', 'class' => 'ajax-modal', 'data_title' => _lang('Log Collection Follow-up')]]])</td>
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
                                        <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Log Follow-up'), 'url' => route('loan_collection_follow_ups.create', $item->repayment_id), 'icon' => 'ti-write', 'class' => 'ajax-modal', 'data_title' => _lang('Log Collection Follow-up')]]])</td>
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
        <div class="tab-pane fade" id="collections">
            <div class="row mb-4">
                @foreach($collectionsBuckets as $bucket)
                    <div class="col-md-4 mb-3">
                        <div class="card workspace-bucket-card mb-0">
                            <div class="card-body">
                                <div class="bucket-label">{{ $bucket->label }}</div>
                                <div class="bucket-value">{{ $bucket->count }}</div>
                                <div class="bucket-meta">{{ _lang('Open repayments in this bucket') }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="alert alert-warning small">
                <strong>{{ _lang('Collections focus') }}:</strong> {{ _lang('Use aging buckets to separate near-term follow-up from longer-running delinquency cases.') }}
            </div>
            <div class="alert alert-light small">
                <strong>{{ _lang('Selected analytics range') }}:</strong> {{ $collectionDateRange['label'] ?? _lang('Today') }}
            </div>
            @unless($followUpsEnabled)
                <div class="alert alert-info small">
                    <strong>{{ _lang('Follow-up tracking setup') }}:</strong> {{ _lang('Run the latest migration to enable saved collection outcomes, promise tracking, and branch completion analytics.') }}
                </div>
            @endunless
            <div class="row mb-4">
                <div class="col-md-4 col-xl-2 mb-3">
                    <div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Follow-ups Logged') }}</div><div class="bucket-value">{{ $followUpsLoggedToday }}</div><div class="bucket-meta">{{ _lang('All collection notes logged in selected range') }}</div></div></div>
                </div>
                <div class="col-md-4 col-xl-2 mb-3">
                    <div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Promises in Range') }}</div><div class="bucket-value">{{ $promiseDueTodayCount }}</div><div class="bucket-meta">{{ _lang('Promised-to-pay cases dated inside selected range') }}</div></div></div>
                </div>
                <div class="col-md-4 col-xl-2 mb-3">
                    <div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Broken Promises') }}</div><div class="bucket-value">{{ $brokenPromisesCount }}</div><div class="bucket-meta">{{ _lang('Outstanding promised-to-pay cases already past due as of range end') }}</div></div></div>
                </div>
                <div class="col-md-4 col-xl-2 mb-3">
                    <div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Recovered') }}</div><div class="bucket-value">{{ $recoveredInRangeCount }}</div><div class="bucket-meta">{{ _lang('Paid repayments that had follow-up activity in selected range') }}</div></div></div>
                </div>
                <div class="col-md-4 col-xl-2 mb-3">
                    <div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Promise Kept') }}</div><div class="bucket-value">{{ $promiseKeptCount }}</div><div class="bucket-meta">{{ _lang('Promised-to-pay cases settled on or before committed date') }}</div></div></div>
                </div>
                <div class="col-md-4 col-xl-2 mb-3">
                    <div class="card workspace-bucket-card mb-0 h-100"><div class="card-body"><div class="bucket-label">{{ _lang('Completion Rate') }}</div><div class="bucket-value">{{ $collectionCompletionRate }}%</div><div class="bucket-meta">{{ _lang('Open due and overdue cases touched in selected range') }}</div></div></div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <div class="workspace-section-title">{{ _lang('Collections Priority Queue') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table table-export mb-0" data-export-filename="Loan_Workspace_Collections_Priority_Queue">
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
                                        <td>
                                            @include('backend.admin.partials.table-actions', [
                                                'items' => [
                                                    ['label' => _lang('Loan'), 'url' => route('loans.show', $item->loan_route_id), 'icon' => 'ti-eye'],
                                                    ['label' => _lang('Log Follow-up'), 'url' => route('loan_collection_follow_ups.create', $item->repayment_id), 'icon' => 'ti-write', 'class' => 'ajax-modal', 'data_title' => _lang('Log Collection Follow-up')],
                                                ],
                                            ])
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted">{{ _lang('No collection priorities found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="workspace-section-title">{{ _lang('Promise Follow-up Queue') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table table-export mb-0" data-export-filename="Loan_Workspace_Promise_Follow_Up_Queue">
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
                                        <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Log Follow-up'), 'url' => route('loan_collection_follow_ups.create', $item->repayment_id), 'icon' => 'ti-write', 'class' => 'ajax-modal', 'data_title' => _lang('Log Collection Follow-up')]]])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No promise follow-up items found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-12 mb-4">
                    <div class="workspace-section-title">{{ _lang('Recent Resolutions') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table table-export mb-0" data-export-filename="Loan_Workspace_Recent_Resolutions">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Loan ID') }}</th>
                                    <th>{{ _lang('Borrower') }}</th>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Paid On') }}</th>
                                    <th>{{ _lang('Resolution') }}</th>
                                    <th>{{ _lang('Last Follow-up') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentResolvedCases as $item)
                                    <tr>
                                        <td>{{ $item->loan_id }}</td>
                                        <td>{{ $item->borrower_name }}</td>
                                        <td>{{ $item->branch_name }}</td>
                                        <td>{{ $item->payment_date }}</td>
                                        <td><span class="workspace-status-chip {{ $item->resolution_theme }}">{{ $item->resolution_label }}</span></td>
                                        <td>
                                            @if($item->last_outcome_label)
                                                <span class="workspace-status-chip {{ $item->last_outcome_theme }}">{{ $item->last_outcome_label }}</span>
                                            @else
                                                <span class="text-muted small">{{ _lang('No log yet') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">{{ _lang('No resolved follow-up cases found for this range') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="workspace-section-title">{{ _lang('Collector Follow-up Performance') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table table-export mb-0" data-export-filename="Loan_Workspace_Collector_Follow_Up_Performance">
                            <thead>
                                <tr>
                                    <th>{{ _lang('User') }}</th>
                                    <th>{{ _lang('Logs') }}</th>
                                    <th>{{ _lang('Cases') }}</th>
                                    <th>{{ _lang('Promises') }}</th>
                                    <th>{{ _lang('Resolved') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($collectorFollowUpPerformance as $collector)
                                    <tr>
                                        <td>{{ $collector->name }}</td>
                                        <td>{{ $collector->logs_count }}</td>
                                        <td>{{ $collector->cases_touched }}</td>
                                        <td>{{ $collector->promised_count }}</td>
                                        <td>{{ $collector->resolved_count }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ _lang('No collector follow-up activity yet') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="workspace-section-title">{{ _lang('Branch Collections Pressure') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table table-export mb-0" data-export-filename="Loan_Workspace_Branch_Collections_Pressure">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Due Today') }}</th>
                                    <th>{{ _lang('Overdue') }}</th>
                                    <th>{{ _lang('Critical') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchCollectionsPressure as $branch)
                                    <tr>
                                        <td>{{ $branch->name }}</td>
                                        <td>{{ $branch->due_today }}</td>
                                        <td>{{ $branch->overdue }}</td>
                                        <td><span class="workspace-status-chip {{ $branch->critical > 0 ? 'critical' : 'active' }}">{{ $branch->critical }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">{{ _lang('No branch collections pressure found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="workspace-section-title">{{ _lang('Branch Follow-up Performance') }}</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered workspace-mini-table table-export mb-0" data-export-filename="Loan_Workspace_Branch_Follow_Up_Performance">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Branch') }}</th>
                                    <th>{{ _lang('Open Queue') }}</th>
                                    <th>{{ _lang('Touched') }}</th>
                                    <th>{{ _lang('Escalated') }}</th>
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
                                        <td>{{ $branch->escalated_today }}</td>
                                        <td>{{ $branch->resolved_in_range }}</td>
                                        <td><span class="workspace-status-chip {{ $branch->completion_rate >= 70 ? 'active' : ($branch->completion_rate >= 40 ? 'review' : 'critical') }}">{{ $branch->completion_rate }}%</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">{{ _lang('No branch follow-up performance yet') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered workspace-mini-table mb-3">
                    <thead>
                        <tr>
                            <th>{{ _lang('Loan ID') }}</th>
                            <th>{{ _lang('Borrower') }}</th>
                            <th>{{ _lang('Missed Date') }}</th>
                            <th>{{ _lang('Amount') }}</th>
                            <th>{{ _lang('Aging') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overdueRepayments as $repayment)
                            @php
                                $daysLate = \Carbon\Carbon::parse($repayment->raw_repayment_date)->diffInDays(\Carbon\Carbon::today());
                            @endphp
                            <tr>
                                <td>{{ $repayment->loan->loan_id }}</td>
                                <td>{{ $repayment->loan->borrower->name }}</td>
                                <td>{{ $repayment->repayment_date }}</td>
                                <td>{{ decimalPlace($repayment->amount_to_pay, optional($repayment->loan->currency)->name) }}</td>
                                <td>{{ $daysLate }} {{ _lang('days late') }}</td>
                                <td>
                                    <span class="workspace-status-chip {{ $daysLate >= 31 ? 'critical' : 'overdue' }}">
                                        {{ $daysLate >= 31 ? _lang('Critical') : _lang('Overdue') }}
                                    </span>
                                </td>
                                <td>@include('backend.admin.partials.table-actions', ['items' => [['label' => _lang('Loan'), 'url' => route('loans.show', $repayment->loan_id), 'icon' => 'ti-eye']]])</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">{{ _lang('No overdue repayments found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="products">
            <div class="loan-tools-layout">
                <div class="loan-tools-sidebar">
                    <div class="nav flex-column nav-pills" id="loan-tools-sidebar" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="loan-tool-products-tab" data-toggle="pill" href="#loan-tool-products" role="tab" aria-controls="loan-tool-products" aria-selected="true">
                            {{ _lang('Loan Products') }}
                            <span class="tool-meta">{{ _lang('Rates, terms, fees') }}</span>
                        </a>
                        <a class="nav-link" id="loan-tool-approvers-tab" data-toggle="pill" href="#loan-tool-approvers" role="tab" aria-controls="loan-tool-approvers" aria-selected="false">
                            {{ _lang('Approver Settings') }}
                            <span class="tool-meta">{{ _lang('Approval levels') }}</span>
                        </a>
                        <a class="nav-link" id="loan-tool-calculator-tab" data-toggle="pill" href="#loan-tool-calculator" role="tab" aria-controls="loan-tool-calculator" aria-selected="false">
                            {{ _lang('Loan Calculator') }}
                            <span class="tool-meta">{{ _lang('Repayment preview') }}</span>
                        </a>
                        <a class="nav-link" id="loan-tool-fields-tab" data-toggle="pill" href="#loan-tool-fields" role="tab" aria-controls="loan-tool-fields" aria-selected="false">
                            {{ _lang('Custom Fields') }}
                            <span class="tool-meta">{{ _lang('Loan profile fields') }}</span>
                        </a>
                    </div>
                </div>

                <div class="tab-content" id="loan-tools-content">
                    <div class="tab-pane fade show active" id="loan-tool-products" role="tabpanel" aria-labelledby="loan-tool-products-tab">
                        <div class="loan-tools-panel-header">
                            <div>
                                <div class="loan-tools-panel-title">{{ _lang('Loan Products') }}</div>
                                <p class="loan-tools-panel-copy">{{ _lang('Manage loan rates, ranges, fees, and product availability without leaving the workspace.') }}</p>
                            </div>
                            <a class="btn btn-primary btn-sm ajax-modal" href="{{ route('loan_products.create') }}" data-title="{{ _lang('Add Loan Product') }}" data-size="lg">
                                <i class="ti-plus mr-1"></i>{{ _lang('Add Product') }}
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table id="loan_products_table" class="table table-bordered workspace-mini-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ _lang('Name') }}</th>
                                        <th>{{ _lang('Amount Range') }}</th>
                                        <th>{{ _lang('Interest') }}</th>
                                        <th>{{ _lang('Type') }}</th>
                                        <th>{{ _lang('Max Term') }}</th>
                                        <th>{{ _lang('Status') }}</th>
                                        <th class="text-center">{{ _lang('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loanProducts as $loanproduct)
                                        @include('backend.admin.loan_product.partials.row', ['loanproduct' => $loanproduct])
                                    @empty
                                        <tr><td colspan="7" class="text-center text-muted">{{ _lang('No loan products found') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="loan-tool-approvers" role="tabpanel" aria-labelledby="loan-tool-approvers-tab">
                        <div class="loan-tools-panel-header">
                            <div>
                                <div class="loan-tools-panel-title">{{ _lang('Approver Settings') }}</div>
                                <p class="loan-tools-panel-copy">{{ _lang('Control who can clear each stage of the loan approval chain.') }}</p>
                            </div>
                            <a class="btn btn-primary btn-sm ajax-modal" href="{{ route('loan_approver_settings.create', ['tenant' => request()->tenant->slug, 'level' => 1]) }}" data-title="{{ _lang('Configure Approvers') }}">
                                <i class="ti-plus mr-1"></i>{{ _lang('Configure') }}
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table id="loan_approver_settings_table" class="table table-bordered workspace-mini-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ _lang('Approval Level') }}</th>
                                        <th>{{ _lang('Approver') }}</th>
                                        <th>{{ _lang('Status') }}</th>
                                        <th class="text-center">{{ _lang('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loanApproverSettings as $setting)
                                        <tr data-id="row_{{ $setting->id }}">
                                            <td>
                                                <strong>{{ _lang($setting->approval_level_name) }}</strong>
                                                <div class="small text-muted">{{ _lang('Level') }} {{ $setting->approval_level }}</div>
                                            </td>
                                            <td>
                                                @if($setting->approver)
                                                    {{ $setting->approver->name }} ({{ $setting->approver->member_no }})
                                                @else
                                                    <span class="text-muted">{{ _lang('Not Assigned') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($setting->status == 1)
                                                    <span class="badge badge-success">{{ _lang('Active') }}</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ _lang('Inactive') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('loan_approver_settings.edit', ['tenant' => request()->tenant->slug, 'id' => $setting->id]) }}" data-title="{{ _lang('Edit Approver Setting') }}" class="btn btn-primary btn-xs ajax-modal">
                                                    <i class="ti-pencil-alt mr-1"></i>{{ _lang('Edit') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">{{ _lang('No approver settings found') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="loan-tool-calculator" role="tabpanel" aria-labelledby="loan-tool-calculator-tab">
                        <div class="loan-tools-panel-header">
                            <div>
                                <div class="loan-tools-panel-title">{{ _lang('Loan Calculator') }}</div>
                                <p class="loan-tools-panel-copy">{{ _lang('Preview repayment schedules while staying inside this workspace.') }}</p>
                            </div>
                        </div>
                        <div id="loan-calculator-workspace-content" class="loan-calculator-launch">
                            @include('backend.admin.loan.modal.calculator', [
                                'first_payment_date' => '',
                                'apply_amount' => '',
                                'interest_rate' => '',
                                'interest_type' => '',
                                'term' => '',
                                'term_period' => '',
                                'late_payment_penalties' => 0,
                                'calculatorContext' => 'workspace',
                                'calculatorTarget' => '#loan-calculator-workspace-content',
                            ])
                        </div>
                    </div>

                    <div class="tab-pane fade" id="loan-tool-fields" role="tabpanel" aria-labelledby="loan-tool-fields-tab">
                        <div class="loan-tools-panel-header">
                            <div>
                                <div class="loan-tools-panel-title">{{ _lang('Loan Custom Fields') }}</div>
                                <p class="loan-tools-panel-copy">{{ _lang('Capture additional loan details needed by CAVIC operations and reporting.') }}</p>
                            </div>
                            <a class="btn btn-primary btn-sm ajax-modal" data-title="{{ _lang('Add New Field') }}" href="{{ route('custom_fields.create') }}?table=loans">
                                <i class="ti-plus mr-1"></i>{{ _lang('Add Field') }}
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table id="custom_fields_table" class="table table-bordered workspace-mini-table mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ _lang('Name') }}</th>
                                        <th>{{ _lang('Field Type') }}</th>
                                        <th>{{ _lang('Status') }}</th>
                                        <th class="text-center">{{ _lang('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loanCustomFields as $customField)
                                        <tr data-id="row_{{ $customField->id }}">
                                            <td class="field_name">{{ $customField->field_name }}</td>
                                            <td class="field_type">
                                                @if($customField->field_type == 'text')
                                                    {{ _lang('Text Box') }}
                                                @elseif($customField->field_type == 'number')
                                                    {{ _lang('Number') }}
                                                @elseif($customField->field_type == 'textarea')
                                                    {{ _lang('Textarea') }}
                                                @elseif($customField->field_type == 'select')
                                                    {{ _lang('Select Box') }}
                                                @elseif($customField->field_type == 'file')
                                                    {{ _lang('File (PNG,JPG,PDF)') }}
                                                @else
                                                    {{ $customField->field_type }}
                                                @endif
                                            </td>
                                            <td class="status">{!! xss_clean(status($customField->status)) !!}</td>
                                            <td class="text-center">
                                                @include('backend.admin.partials.table-actions', [
                                                    'items' => [
                                                        ['label' => _lang('Edit'), 'url' => route('custom_fields.edit', ['tenant' => request()->tenant->slug, 'custom_field' => $customField->id]), 'icon' => 'ti-pencil-alt', 'class' => 'ajax-modal', 'data_title' => _lang('Update Custom Field')],
                                                        ['label' => _lang('Delete'), 'url' => route('custom_fields.destroy', ['tenant' => request()->tenant->slug, 'custom_field' => $customField->id]), 'icon' => 'ti-trash', 'method' => 'delete', 'class' => 'btn-remove', 'form_class' => 'ajax-remove'],
                                                    ],
                                                ])
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">{{ _lang('No custom fields found') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script>
(function ($) {
    "use strict";

    function initLoanCalculator($scope) {
        if (typeof init_datepicker === 'function') {
            init_datepicker($scope);
        }

        $($scope).find('.auto-select').each(function () {
            $(this).val($(this).data('selected')).trigger('change');
        });

        $($scope).find('.validate').parsley();
    }

    $(document).on('change', '[data-loan-calculator-interest-type]', function () {
        var $calculator = $(this).closest('.loan-calculator-modal-content');
        var isOneTime = $(this).val() === 'one_time';
        var $term = $calculator.find('[data-loan-calculator-term]');
        var $termPeriod = $calculator.find('[data-loan-calculator-term-period]');

        $term.val(isOneTime ? 1 : $term.val()).prop('readonly', isOneTime);
        $termPeriod.val(isOneTime ? '+1 day' : $termPeriod.val()).prop('disabled', isOneTime);
    });

    $(document).on('submit', '.loan-calculator-ajax-form', function (event) {
        event.preventDefault();

        var form = this;
        var $form = $(form);
        var $modalBody = $form.closest('.modal-body');
        var $target = $form.data('target') ? $($form.data('target')) : $modalBody;
        var $alertContext = $modalBody.length ? $modalBody.closest('.modal-content') : $target;

        $form.find('button[type=submit]').prop('disabled', true);

        $.ajax({
            method: 'POST',
            url: $form.attr('action'),
            data: new FormData(form),
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $('#preloader').css('display', 'block');
            },
            success: function (response) {
                $('#preloader').css('display', 'none');
                $form.find('button[type=submit]').prop('disabled', false);

                if (typeof response === 'object' && response.result === 'error') {
                    var message = Array.isArray(response.message) ? response.message.join('<br>') : response.message;
                    $alertContext.find('.alert-danger').html('<span>' + message + '</span>').removeClass('d-none');
                    return;
                }

                $target.html(response);
                initLoanCalculator($target);
            },
            error: function (request, status, error) {
                $('#preloader').css('display', 'none');
                $form.find('button[type=submit]').prop('disabled', false);
                var message = request.responseJSON && request.responseJSON.message
                    ? request.responseJSON.message
                    : (request.responseText ? request.responseText.replace(/(<([^>]+)>)/ig, '') : (error || 'Error'));
                $alertContext.find('.alert-danger').html('<span>' + message + '</span>').removeClass('d-none');
            }
        });
    });
})(window.jQuery || window.$);
</script>
@endsection

@php
$inbox = request_count('messages');
$member_requests_count = request_count('member_requests');
$pending_loans_count = request_count('pending_loans');
$deposit_requests_count = request_count('deposit_requests');
$withdraw_requests_count = request_count('withdraw_requests');
$upcomming_repayments_count = request_count('upcomming_repayments');

$member_requests_badge = request_count('member_requests', true);
$pending_loans_badge = request_count('pending_loans', true);
$action_center_total = $member_requests_count + $pending_loans_count + $deposit_requests_count + $withdraw_requests_count + $upcomming_repayments_count;
$finance_queue_total = $deposit_requests_count + $withdraw_requests_count;
@endphp

@include('layouts.menus.admin.dashboard')
@include('layouts.menus.admin.action-center')
@include('layouts.menus.admin.members')
@include('layouts.menus.admin.loans')
@include('layouts.menus.admin.finance')
@include('layouts.menus.admin.reports')
@include('layouts.menus.admin.administration')
@include('layouts.menus.admin.messages')

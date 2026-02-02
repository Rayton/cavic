@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<span class="panel-title">{{ _lang('My Loans') }}</span>
				<a class="btn btn-primary btn-xs float-right" href="{{ route('loans.apply_loan') }}"><i class="ti-plus"></i>&nbsp;{{ _lang('Apply Loan') }}</a>
			</div>

			<div class="card-body">
				<table id="loans_table" class="table table-bordered data-table table-export">
					<thead>
						<tr>
                            <th data-total-label="{{ _lang('Total') }}">{{ _lang('Loan ID') }}</th>
                            <th>{{ _lang('Loan Product') }}</th>
                            <th>{{ _lang('Currency') }}</th>
                            <th class="text-right" data-sum="1">{{ _lang('Applied Amount') }}</th>
                            <th class="text-right" data-sum="1">{{ _lang('Amount Paid') }}</th>
                            <th class="text-right" data-sum="1">{{ _lang('Due Amount') }}</th>
                            <th>{{ _lang('Release Date') }}</th>
                            <th>{{ _lang('Status') }}</th>
						</tr>
					</thead>
					<tbody>
                        @foreach($loans as $loan)
                        <tr>
                            <td><a href="{{ route('loans.loan_details',$loan->id) }}">{{ $loan->loan_id }}</a></td>
                            <td>{{ $loan->loan_product->name }}</td>
                            <td>{{ $loan->currency->name }}</td>
                            <td class="text-right">{{ decimalPlace($loan->applied_amount, currency($loan->currency->name)) }}</td>
                            <td class="text-right">{{ decimalPlace($loan->total_paid, currency($loan->currency->name)) }}</td>
                            <td class="text-right">{{ decimalPlace($loan->applied_amount - $loan->total_paid, currency($loan->currency->name)) }}</td>
                            <td>{{ $loan->release_date }}</td>
                            <td>
                                @if($loan->status == 0)
                                    <a href="#" class="approval-status-link" data-loan-id="{{ $loan->id }}" data-toggle="modal" data-target="#approvalStatusModal">
                                        {!! xss_clean(show_status(_lang('Pending'), 'warning')) !!}
                                    </a>
                                @elseif($loan->status == 1)
                                    {!! xss_clean(show_status(_lang('Approved'), 'success')) !!}
                                @elseif($loan->status == 2)
                                    {!! xss_clean(show_status(_lang('Completed'), 'info')) !!}
                                @elseif($loan->status == 3)
                                    {!! xss_clean(show_status(_lang('Cancelled'), 'danger')) !!}
                                @endif
                            </td>
                        </tr>
                        @endforeach
					</tbody>
					<tfoot><tr class="table-totals-row"><td></td><td></td><td></td><td class="text-right"></td><td class="text-right"></td><td class="text-right"></td><td></td><td></td></tr></tfoot>
				</table>
			</div>
		</div>
	</div>
</div>

<!-- Approval Status Modal -->
<div class="modal fade" id="approvalStatusModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ _lang('Loan Approval Status') }}</h5>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body" id="approvalStatusContent">
				<!-- Content will be loaded dynamically -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ _lang('Close') }}</button>
			</div>
		</div>
	</div>
</div>

<!-- Approval Status Modal -->
<div class="modal fade" id="approvalStatusModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ _lang('Loan Approval Status') }}</h5>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body" id="approvalStatusContent">
				<!-- Content will be loaded dynamically -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ _lang('Close') }}</button>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js-script')
@php
	$loansData = $loans->map(function($loan) {
		$currencyName = $loan->currency ? $loan->currency->name : '';
		return [
			'id' => $loan->id,
			'loan_id' => $loan->loan_id,
			'loan_product' => $loan->loan_product ? $loan->loan_product->name : null,
			'currency' => $currencyName,
			'amount_formatted' => decimalPlace($loan->applied_amount ?? 0, currency($currencyName)),
			'approvals' => $loan->approvals->map(function($approval) {
				return [
					'approval_level' => $approval->approval_level,
					'approval_level_name' => $approval->approval_level_name,
					'status' => $approval->status,
					'remarks' => $approval->remarks,
					'approved_at' => $approval->formatted_approved_at,
					'approver' => $approval->approver ? [
						'name' => $approval->approver->name,
						'member_no' => $approval->approver->member_no
					] : null
				];
			})->values()
		];
	})->keyBy('id');
@endphp

<script>
$(document).ready(function() {
	var loansData = @json($loansData);

	$('.approval-status-link').on('click', function(e) {
		e.preventDefault();
		var loanId = $(this).data('loan-id');
		var loanData = loansData[loanId];

		if (loanData) {
			var approvals = loanData.approvals || [];
			var html = '<div class="approval-status-container">';

			html += '<div class="row mb-3">';
			html += '<div class="col-md-12">';
			html += '<h6><strong>' + (loanData.loan_id || 'N/A') + '</strong></h6>';
			html += '<p class="text-muted mb-0">' + (loanData.loan_product || 'N/A') + ' - ' + (loanData.currency || 'N/A') + '</p>';
			html += '<p class="mb-0 mt-1"><strong>{{ _lang("Loan Amount") }}:</strong> ' + (loanData.amount_formatted || '-') + '</p>';
			html += '</div>';
			html += '</div>';

			html += '<table class="table table-bordered">';
			html += '<thead>';
			html += '<tr>';
			html += '<th>{{ _lang("Approval Level") }}</th>';
			html += '<th>{{ _lang("Approver") }}</th>';
			html += '<th>{{ _lang("Status") }}</th>';
			html += '<th>{{ _lang("Date") }}</th>';
			html += '<th>{{ _lang("Remarks") }}</th>';
			html += '</tr>';
			html += '</thead>';
			html += '<tbody>';

			// Define approval levels
			var levels = [
				{ level: 1, name: '{{ _lang("Trustee 1") }}' },
				{ level: 2, name: '{{ _lang("Trustee 2") }}' },
				{ level: 3, name: '{{ _lang("Secretary") }}' },
				{ level: 4, name: '{{ _lang("Chairman") }}' }
			];

			levels.forEach(function(levelInfo) {
				var approval = approvals.find(function(a) { return a.approval_level == levelInfo.level; });

				html += '<tr>';
				html += '<td><strong>' + levelInfo.name + '</strong></td>';

				if (approval && approval.approver) {
					html += '<td>' + (approval.approver.name || 'N/A') + ' (' + (approval.approver.member_no || 'N/A') + ')</td>';
				} else {
					html += '<td class="text-muted">{{ _lang("Not Assigned") }}</td>';
				}

				if (approval) {
					if (approval.status == 1) {
						html += '<td><span class="badge badge-success">{{ _lang("Approved") }}</span></td>';
					} else if (approval.status == 2) {
						html += '<td><span class="badge badge-danger">{{ _lang("Rejected") }}</span></td>';
					} else {
						html += '<td><span class="badge badge-warning">{{ _lang("Pending") }}</span></td>';
					}

					if (approval.approved_at) {
						html += '<td>' + approval.approved_at + '</td>';
					} else {
						html += '<td class="text-muted">-</td>';
					}

					html += '<td>' + (approval.remarks || '<span class="text-muted">-</span>') + '</td>';
				} else {
					html += '<td><span class="badge badge-secondary">{{ _lang("Not Started") }}</span></td>';
					html += '<td class="text-muted">-</td>';
					html += '<td class="text-muted">-</td>';
				}

				html += '</tr>';
			});

			html += '</tbody>';
			html += '</table>';

			// Show progress bar
			var approvedCount = approvals.filter(function(a) { return a.status == 1; }).length;
			var totalCount = 4;
			var percentage = (approvedCount / totalCount) * 100;

			html += '<div class="mt-3">';
			html += '<h6>{{ _lang("Approval Progress") }}</h6>';
			html += '<div class="progress" style="height: 30px;">';
			html += '<div class="progress-bar" role="progressbar" style="width: ' + percentage + '%">';
			html += approvedCount + '/' + totalCount + ' {{ _lang("Approved") }}';
			html += '</div>';
			html += '</div>';
			html += '</div>';

			html += '</div>';

			$('#approvalStatusContent').html(html);
		}
	});
});
</script>
@endsection

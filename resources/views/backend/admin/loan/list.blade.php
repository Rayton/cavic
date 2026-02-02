@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header d-flex align-items-center">
				<span class="panel-title">
					{{ _lang('Loans') }}
				</span>
				<div class="ml-auto d-flex align-items-center">
					<select name="status" class="select-filter filter-select auto-select mr-2" data-selected="{{ $status }}">
						<option value="">{{ _lang('All') }}</option>
						<option value="0">{{ _lang('Pending') }}</option>
						<option value="1">{{ _lang('Approved') }}</option>
						<option value="2">{{ _lang('Completed') }}</option>
					</select>
					<a class="btn btn-primary btn-xs" href="{{ route('loans.create') }}"><i class="ti-plus"></i>&nbsp;{{ _lang('Add New') }}</a>
				</div>
			</div>

			<div class="card-body">
				<table id="loans_table" class="table table-bordered table-export">
					<thead>
						<tr>
							<th data-total-label="{{ _lang('Total') }}">{{ _lang('Loan ID') }}</th>
							<th>{{ _lang('Loan Product') }}</th>
							<th>{{ _lang('Borrower') }}</th>
							<th>{{ _lang('Member No') }}</th>
							<th>{{ _lang('Release Date') }}</th>
							<th class="text-right" data-sum="1">{{ _lang('Applied Amount') }}</th>
							<th>{{ _lang('Approval Status') }}</th>
							<th>{{ _lang('Loan Status') }}</th>
							<th class="text-center" data-no-export="1">{{ _lang('Action') }}</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
					<tfoot><tr class="table-totals-row"><td></td><td></td><td></td><td></td><td></td><td class="text-right"></td><td></td><td></td><td></td></tr></tfoot>
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
@endsection

@section('js-script')
<script src="{{ asset('public/backend/assets/js/datatables/loans.js?v=1.1') }}"></script>
<script>
$(document).ready(function() {
	// Handle approval status link click
	$(document).on('click', '.approval-status-link', function(e) {
		e.preventDefault();
		var loanId = $(this).data('loan-id');
		var $modal = $('#approvalStatusModal');
		var $content = $('#approvalStatusContent');

		// Show loading
		$content.html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> {{ _lang("Loading...") }}</div>');
		$modal.modal('show');

		// Fetch approval data via AJAX
		$.ajax({
			url: _tenant_url + '/loans/' + loanId + '/approval-data',
			method: 'GET',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function(response) {
				if (response.success && response.data) {
					displayApprovalModal(response.data);
				} else {
					$content.html('<div class="alert alert-danger">{{ _lang("Error loading approval data") }}</div>');
				}
			},
			error: function() {
				$content.html('<div class="alert alert-danger">{{ _lang("Error loading approval data") }}</div>');
			}
		});
	});

	function displayApprovalModal(loanData) {
			var approvals = loanData.approvals || [];
			var html = '<div class="approval-status-container">';

			html += '<div class="row mb-3">';
			html += '<div class="col-md-12">';
			html += '<h6><strong>' + (loanData.loan_id || 'N/A') + '</strong></h6>';
			html += '<p class="text-muted mb-0">' + (loanData.loan_product || 'N/A') + ' - ' + (loanData.currency || 'N/A') + '</p>';
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
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<span class="panel-title">{{ _lang('Expense Report') }}</span>
			</div>

			<div class="card-body">
				<div class="report-params">
					<form class="validate" method="post" action="{{ route('reports.expense_report') }}">
						<div class="row">
              				@csrf

							<div class="col-xl-2 col-lg-4">
								<div class="form-group">
									<label class="control-label">{{ _lang('Start Date') }}</label>
									<input type="text" class="form-control datepicker" name="date1" id="date1" value="{{ isset($date1) ? $date1 : old('date1') }}" readOnly="true" required>
								</div>
							</div>

							<div class="col-xl-2 col-lg-4">
								<div class="form-group">
									<label class="control-label">{{ _lang('End Date') }}</label>
									<input type="text" class="form-control datepicker" name="date2" id="date2" value="{{ isset($date2) ? $date2 : old('date2') }}" readOnly="true" required>
								</div>
							</div>

							<div class="col-xl-3 col-lg-4">
								<div class="form-group">
								<label class="control-label">{{ _lang('Expense Category') }}</label>
									<select class="form-control auto-select" data-selected="{{ isset($category) ? $category : old('category') }}" name="category">
										<option value="">{{ _lang('All') }}</option>
										@foreach($expense_categories as $expense_category)
											<option value="{{ $expense_category->id }}">{{ $expense_category->name }}</option>
										@endforeach
									</select>
								</div>
							</div>

							@if(auth()->user()->user_type == 'admin')
							<div class="col-xl-3 col-lg-4">
								<div class="form-group">
								<label class="control-label">{{ _lang('Branch') }}</label>
									<select class="form-control auto-select" data-selected="{{ isset($branch) ? $branch : old('branch') }}" name="branch">
										<option value="">{{ _lang('All') }}</option>
										@foreach(\App\Models\Branch::all() as $branch)
										<option value="{{ $branch->id }}">{{ $branch->name }}</option>
										@endforeach
									</select>
								</div>
							</div>
							@endif

							<div class="col-xl-2 col-lg-4">
								<button type="submit" class="btn btn-light btn-xs btn-block mt-26"><i class="ti-filter"></i>&nbsp;{{ _lang('Filter') }}</button>
							</div>
						</form>

					</div>
				</div><!--End Report param-->

				@php $date_format = get_date_format(); @endphp
				@php $currency = currency(get_base_currency()); @endphp

				<div class="report-header">
				   <img src="{{ get_logo() }}" class="logo"/>
				   <h4>{{ _lang('Expense Report') }}</h4>
				   <h5>{{ isset($date1) ? date($date_format, strtotime($date1)).' '._lang('to').' '.date($date_format, strtotime($date2)) : '----------  '._lang('to').'  ----------' }}</h5>
				</div>

				<table class="table table-bordered report-table table-export">
					<thead>
						<tr>
							<th data-total-label="{{ _lang('Total') }}">{{ _lang('Date') }}</th>
							<th>{{ _lang('Reference') }}</th>
							<th>{{ _lang('Expense Type') }}</th>
							<th class="text-right" data-sum="1">{{ _lang('Amount') }}</th>
						</tr>
					</thead>
					<tbody>
					@if(isset($report_data))
						@foreach($report_data as $expense)
							<tr>
								<td>{{ $expense->expense_date }}</td>
								<td>{{ $expense->reference }}</td>
								<td>{{ $expense->expense_category->name }}</td>
								<td class="text-right">{{ decimalPlace($expense->amount, $currency) }}</td>
							</tr>
						@endforeach
					@endif
				    </tbody>
					<tfoot><tr class="table-totals-row"><td></td><td></td><td></td><td class="text-right"></td></tr></tfoot>
				</table>
			</div>
		</div>
	</div>
</div>

@endsection

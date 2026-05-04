@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<span class="panel-title">{{ _lang('Loan Products') }}</span>
				<a class="btn btn-primary btn-xs float-right ajax-modal" href="{{ route('loan_products.create') }}" data-title="{{ _lang('Add Loan Product') }}" data-size="lg"><i class="ti-plus"></i>&nbsp;{{ _lang('Add New') }}</a>
			</div>
			<div class="card-body">
				<table id="loan_products_table" class="table table-bordered data-table">
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
						@foreach($loanproducts as $loanproduct)
							@include('backend.admin.loan_product.partials.row', ['loanproduct' => $loanproduct])
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
@endsection

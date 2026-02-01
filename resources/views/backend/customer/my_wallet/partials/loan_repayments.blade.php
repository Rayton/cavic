@php
	$currencyName = $loan->currency ? $loan->currency->name : '';
	// Total loan = principal + interest when total_payable is set, else principal only
	$totalAmount = (float) ($loan->total_payable ?? $loan->applied_amount ?? 0);
	// Remaining balance = total (incl. interest) minus sum of all repayment transactions
	$sumRepaid = (float) $transactions->sum('amount');
	$remainingBalance = $totalAmount - $sumRepaid;
	$tenantSlug = request()->route('tenant');
@endphp
<div class="table-responsive">
	<p class="mb-2">
		<strong>{{ _lang('Loan') }}</strong>:
		{{ _lang('Total Amount') }}: {{ decimalPlace($totalAmount, currency($currencyName)) }},
		{{ _lang('Remaining Balance') }}: {{ decimalPlace($remainingBalance, currency($currencyName)) }}
	</p>
	<table class="table table-bordered table-sm">
		<thead>
			<tr>
				<th>{{ _lang('Date') }}</th>
				<th>{{ _lang('Account') }}</th>
				<th class="text-right">{{ _lang('Amount') }}</th>
				<th class="text-right">{{ _lang('Remaining Balance') }}</th>
				<th>{{ _lang('Status') }}</th>
				<th class="text-center">{{ _lang('Details') }}</th>
			</tr>
		</thead>
		<tbody>
			@php $balanceAfter = $totalAmount; @endphp
			@forelse($transactions as $transaction)
			@php
				$currencyName = $transaction->account && $transaction->account->savings_type && $transaction->account->savings_type->currency ? $transaction->account->savings_type->currency->name : ($loan->currency ? $loan->currency->name : '');
				$balanceAfter = $balanceAfter - $transaction->amount;
			@endphp
			<tr>
				<td>{{ $transaction->trans_date }}</td>
				<td>{{ $transaction->account ? $transaction->account->account_number : '-' }}</td>
				<td class="text-right">{{ decimalPlace($transaction->amount, currency($currencyName)) }}</td>
				<td class="text-right">{{ decimalPlace($balanceAfter, currency($currencyName)) }}</td>
				<td>{!! xss_clean(transaction_status($transaction->status)) !!}</td>
				<td class="text-center"><a href="{{ $tenantSlug ? route('trasnactions.details', ['tenant' => $tenantSlug, 'id' => $transaction->id]) : url('/') }}" target="_blank" class="btn btn-outline-primary btn-xs">{{ _lang('View') }}</a></td>
			</tr>
			@empty
			<tr>
				<td colspan="6" class="text-center">{{ _lang('No repayment transactions found') }}</td>
			</tr>
			@endforelse
		</tbody>
	</table>
</div>

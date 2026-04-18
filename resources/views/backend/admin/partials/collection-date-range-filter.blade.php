@include('backend.admin.partials.filter-bar', [
    'title' => _lang('Collections Analytics Date Range'),
    'description' => _lang('Filter follow-up execution metrics, promise tracking, and productivity views by date range.'),
    'filterId' => $filterId ?? ('collection-date-range-' . uniqid()),
    'dateRange' => $collectionDateRange ?? [],
])

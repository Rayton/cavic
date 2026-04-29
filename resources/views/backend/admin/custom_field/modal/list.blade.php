<div class="member-detail-modal">
    <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
        <div>
            <h6 class="mb-1">{{ _lang('Custom Fields') }}</h6>
            <div class="text-muted small">{{ _lang('Configure extra member profile fields without leaving the Members workspace.') }}</div>
        </div>
        <a class="btn btn-primary btn-xs ajax-modal-2" data-title="{{ _lang('Add New Field') }}" href="{{ route('custom_fields.create') }}?table={{ $table }}">
            <i class="ti-plus mr-1"></i>{{ _lang('Add New') }}
        </a>
    </div>

    <div class="member-compact-card">
        <div class="table-responsive">
            <table id="custom_fields_table" class="table table-bordered table-striped member-compact-table mb-0">
                <thead>
                    <tr>
                        <th>{{ _lang('Name') }}</th>
                        <th>{{ _lang('Field Type') }}</th>
                        <th>{{ _lang('Status') }}</th>
                        <th class="text-center">{{ _lang('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customFields as $customField)
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
                                        ['label' => _lang('Edit'), 'url' => route('custom_fields.edit', ['tenant' => request()->tenant->slug, 'custom_field' => $customField->id]), 'icon' => 'ti-pencil-alt', 'class' => 'ajax-modal-2', 'data_title' => _lang('Update Custom Field')],
                                        ['label' => _lang('Delete'), 'url' => route('custom_fields.destroy', ['tenant' => request()->tenant->slug, 'custom_field' => $customField->id]), 'icon' => 'ti-trash', 'method' => 'delete', 'class' => 'btn-remove'],
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

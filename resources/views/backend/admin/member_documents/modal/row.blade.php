<tr data-id="row_{{ $memberdocument->id }}">
    @if(($context ?? 'list') === 'quick_view')
        <td class="name">{{ $memberdocument->name }}</td>
        <td class="document"><a target="_blank" href="{{ asset('public/uploads/documents/'.$memberdocument->document) }}">{{ $memberdocument->document }}</a></td>
        <td class="created_at">{{ $memberdocument->created_at }}</td>
    @else
        <td class="user_id">{{ optional($memberdocument->member)->first_name.' '.optional($memberdocument->member)->last_name }}</td>
        <td class="name">{{ $memberdocument->name }}</td>
        <td class="document"><a target="_blank" href="{{ asset('public/uploads/documents/'.$memberdocument->document) }}">{{ $memberdocument->document }}</a></td>
    @endif
    <td class="text-center">
        @include('backend.admin.partials.table-actions', [
            'items' => [
                ['label' => _lang('Edit'), 'url' => route('member_documents.edit', ['tenant' => request()->tenant->slug, 'member_document' => $memberdocument->id]) . '?context=' . ($context ?? 'list'), 'icon' => 'ti-pencil-alt', 'class' => 'ajax-modal-2', 'data_title' => _lang('Update Document')],
                ['label' => _lang('Delete'), 'url' => route('member_documents.destroy', ['tenant' => request()->tenant->slug, 'member_document' => $memberdocument->id]), 'icon' => 'ti-trash', 'method' => 'delete', 'class' => 'btn-remove', 'form_class' => 'ajax-remove'],
            ],
        ])
    </td>
</tr>
